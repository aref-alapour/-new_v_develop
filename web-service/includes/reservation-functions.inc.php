<?php
/** View helpers and lock helpers for reservation dispatch. */
function operator_desktop_view($args)
{
    global $conn;

    $time_res     = $args->time_res;
    $service_id   = $args->service_id;
    $user_role    = $args->user_role;
    $day_type     = $args->day_type;
    $bookings     = $args->bookings;
    $sanses       = $args->sanses;
    $auto_disable = $args->auto_disable;

    if ($user_role == "admin" || $user_role == "shop_manager" || $user_role == "shopist" || $user_role == 'contentist') {
        $auto_disable = 0;
    } ?>

    <div class="container">
        <div class="row">

            <?php
            $sanses_list = [];
            $sans_objs   = [];
            $order_objs  = [];

            foreach ($sanses[$day_type] as $sans) {
                $sanses_list[] = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
            }

            $sanses_list = implode(',', $sanses_list);

            if ($sanses_list !== '') {
                $result    = $conn->query(sprintf("SELECT status, booking_time, name, phone, quantity FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` IN (%s)", $service_id, $sanses_list));

                if ($result->num_rows > 0) {
                    $sans_objs = $result->fetch_all(MYSQLI_ASSOC);
                }
            }

            foreach ($sans_objs as $sans_obj) {
                $order_objs[(string) $sans_obj['booking_time']] = $sans_obj;
            }

            foreach ($sanses[$day_type] as $sans) :

                $firstTimeTs = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
                $price = ! empty($sans['off_price']) ? $sans['off_price'] : $sans['price'];

                $order_obj = isset( $order_objs[ $firstTimeTs ] ) ? $order_objs[ $firstTimeTs ] : null;

                if ( $order_obj && isset( $order_obj['status'] ) && (int) $order_obj['status'] === 1 ) : // Ø¨Ø³ØªÙ‡ Ø´Ø¯Ù‡ ØªÙˆØ³Ø· Ù…Ø¬Ù…ÙˆØ¹Ù‡ Ø¯Ø§Ø±
            ?>
                    <div class="col-4 col-md-3 res-col res-col-<?php echo $firstTimeTs; ?> reserved-bg">
                        <?php
                        if ($user_role == "admin" || $user_role == "shop_manager" || $user_role == "shopist" || $user_role == 'contentist'): ?>
                            <a href="javascript:" id="sans_exchange_btn" data-time="<?php echo $firstTimeTs; ?>" data-display="<?php echo jdate('l j F Ø³Ø§Ø¹Øª H:i', $firstTimeTs); ?>">
                                <i class="sans-exchange icofont-exchange"></i>
                            </a>

                            <a href="javascript:" id="sans_remove_btn" data-time="<?php echo $firstTimeTs; ?>" data-display="<?php echo jdate('l j F Ø³Ø§Ø¹Øª H:i', $firstTimeTs); ?>">
                                <i class="sans-remove icofont-error"></i>
                            </a>
                        <?php
                        endif ?>

                        <div class="tac">Ø³Ø§Ø¹Øª
                            <span> <?php echo jdate('H:i', $firstTimeTs) ?></span>
                            <br>

                            <?php
                            if (! empty($sans['off_price'])) : ?>
                                <p class="tac">Ù†ÙØ±ÛŒ
                                    <span class='green-c'><?php echo number_format($sans['off_price']); ?></span>
                                    <span> ØªÙˆÙ…Ø§Ù†</span>
                                </p>
                                <p class="tac">
                                    <span>
                                        <del><?php echo number_format($sans['price']); ?></del>
                                    </span>
                                    <span> ØªÙˆÙ…Ø§Ù†</span>
                                </p>

                            <?php
                            else: ?>
                                <p class="tac">Ù†ÙØ±ÛŒ
                                    <span class='green-c'><?php echo number_format($sans['price']); ?></span>
                                    <span> ØªÙˆÙ…Ø§Ù†</span>
                                </p>
                                <p class="tac">
                                    <span></span>&nbsp;<span></span>
                                </p>

                            <?php
                            endif; ?>

                            <p class="tac">
                                <?php
                                $player_name  = $order_obj['name'];
                                $player_phone = $order_obj['phone'];

                                echo $player_name;
                                echo "<br>" . $player_phone;
                                echo "<br>" . $order_obj['quantity'] . "Ã— Ù†ÙØ±"; ?>

                                <input type="hidden" class="room_single_operator_sans_player_name" value="<?php echo $player_name ?>">
                                <input type="hidden" class="room_single_operator_sans_player_phone" value="<?php echo $player_phone ?>">
                            </p>
                            <p class="tac">&nbsp</p>
                        </div>
                    </div>

                <?php
                elseif ( $order_obj && isset( $order_obj['status'] ) && (int) $order_obj['status'] === 2): // Ø±Ø²Ø±Ùˆ Ø´Ø¯Ù‡ ØªÙˆØ³Ø· ÛŒÙˆØ²Ø±
                ?>
                    <div class="col-4 col-md-3 res-col res-col-<?php echo $firstTimeTs; ?>">
                        <p class="tac">Ø³Ø§Ø¹Øª
                            <span><?php echo jdate('H:i', $firstTimeTs) ?></span>
                            <br>
                        <p class="tac">ØºÛŒØ± Ù‚Ø§Ø¨Ù„ Ø±Ø²Ø±Ùˆ</p>
                        <p>&nbsp;</p>
                        <input type="button" class="btn btn-success btn-sm btn-block green-bg fff-c"
                            data-start="<?php echo $firstTimeTs ?>" value="Ø¨Ø§Ø²Ú©Ù†" data-pricesel="<?php echo $price; ?>"
                            id="btn_<?php echo $firstTimeTs ?>" data-status="open"
                            data-service="<?php echo $service_id; ?>" onclick="time_click_bazkon(this.id)"
                            <?php
                            echo $firstTimeTs < $auto_disable ? 'disabled' : ''; ?>>
                    </div>

                <?php
                else: ?>
                    <div class="col-4 col-md-3 res-col res-col-<?php echo $firstTimeTs; ?> ghadr">

                        <p class="tac">Ø³Ø§Ø¹Øª
                            <span><?php echo jdate('H:i', $firstTimeTs) ?></span>
                            <br>
                        <p class="tac"> Ù‚Ø§Ø¨Ù„ Ø±Ø²Ø±Ùˆ</p>
                        <p class="tac">&nbsp;</p>

                        <?php
                        if (in_array($firstTimeTs, $bookings)) { ?>
                            <p class="btn btn-success btn-sm btn-block fff-c" style="width: 100%;border: none;border-radius: 4px;background: #ff8d00;cursor: not-allowed;">
                                Ø¯Ø± Ø­Ø§Ù„ Ø±Ø²Ø±Ùˆ</p>

                        <?php
                        } else { // Ù†Ù‡ Ø¨Ø³ØªÙ‡ Ø´Ø¯Ù‡ Ù†Ù‡ Ø±Ø²Ø±Ùˆ Ø´Ø¯Ù‡
                        ?>
                            <input type="button" class="btn btn-success btn-sm btn-block red-bg fff-c"
                                data-start="<?php echo $firstTimeTs ?>" value="Ø­Ø°Ù Ú©Ù†"
                                data-pricesel="<?php echo $price; ?>"
                                id="btn_<?php echo $firstTimeTs ?>" data-status="2"
                                data-service="<?php echo $service_id; ?>" onclick="time_click_hazf(this.id)"
                                <?php
                                echo $firstTimeTs < $auto_disable ? 'disabled' : ''; ?>>
                        <?php
                        } ?>

                        </p>
                    </div>

            <?php
                endif;

            endforeach; ?>

        </div>
    </div>

<?php
}

/********************************************************************************************************************************/
function operator_mobile_view($args)
{
    global $conn;

    $time_res     = $args->time_res;
    $service_id   = $args->service_id;
    $user_role    = $args->user_role;
    $day_type     = $args->day_type;
    $bookings     = $args->bookings;
    $sanses       = $args->sanses;
    $auto_disable = $args->auto_disable;

    if ($user_role == "admin" || $user_role == "shop_manager" || $user_role == "shopist" || $user_role == 'contentist') {
        $auto_disable = 0;
    } ?>

    <div class="container">
        <div class="row">

            <?php
            $sanses_list = [];
            $sans_objs   = [];
            $order_objs  = [];

            foreach ($sanses[$day_type] as $sans) {
                $sanses_list[] = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
            }

            $sanses_list = implode(',', $sanses_list);

            if ($sanses_list !== '') {
                $result    = $conn->query(sprintf("SELECT status, booking_time, name, phone, quantity FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` IN (%s)", $service_id, $sanses_list));

                if ($result->num_rows > 0) {
                    $sans_objs = $result->fetch_all(MYSQLI_ASSOC);
                }
            }

            foreach ($sans_objs as $sans_obj) {
                $order_objs[(string) $sans_obj['booking_time']] = $sans_obj;
            }

            foreach ($sanses[$day_type] as $sans) :

                $firstTimeTs = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
                $price = ! empty($sans['off_price']) ? $sans['off_price'] : $sans['price'];

                $order_obj = isset( $order_objs[ $firstTimeTs ] ) ? $order_objs[ $firstTimeTs ] : null;

                if ( $order_obj && isset( $order_obj['status'] ) && (int) $order_obj['status'] === 1 ): // Ø¨Ø³ØªÙ‡ Ø´Ø¯Ù‡ ØªÙˆØ³Ø· Ù…Ø¬Ù…ÙˆØ¹Ù‡ Ø¯Ø§Ø±
            ?>
                    <div class="col-6 col-md-3 res-col res-col res-col-<?php echo $firstTimeTs; ?> reserved-bg">
                        <?php
                        if ($user_role == "admin" || $user_role == "shop_manager" || $user_role == "shopist" || $user_role == 'contentist'): ?>
                            <a href="javascript:" id="sans_exchange_btn" data-time="<?php echo $firstTimeTs; ?>" data-display="<?php echo jdate('l j F Ø³Ø§Ø¹Øª H:i', $firstTimeTs); ?>">
                                <i class="sans-exchange icofont-exchange"></i>
                            </a>

                            <a href="javascript:" id="sans_remove_btn" data-time="<?php echo $firstTimeTs; ?>" data-display="<?php echo jdate('l j F Ø³Ø§Ø¹Øª H:i', $firstTimeTs); ?>">
                                <i class="sans-remove icofont-error"></i>
                            </a>
                        <?php
                        endif ?>

                        <div class="tac">Ø³Ø§Ø¹Øª
                            <span><?php echo jdate('H:i', $firstTimeTs) ?></span>
                            <br>

                            <?php
                            if (! empty($sans['off_price'])) : ?>
                                <p class="tac">Ù†ÙØ±ÛŒ
                                    <span class='green-c'><?php echo number_format($sans['off_price']); ?></span>
                                    <span> ØªÙˆÙ…Ø§Ù†</span>
                                </p>
                                <p class="tac">
                                    <span>
                                        <del><?php echo number_format($sans['price']); ?></del>
                                    </span>
                                    <span> ØªÙˆÙ…Ø§Ù†</span>
                                </p>

                            <?php
                            else: ?>
                                <p class="tac">Ù†ÙØ±ÛŒ
                                    <span class='green-c'><?php echo number_format($sans['price']); ?></span>
                                    <span> ØªÙˆÙ…Ø§Ù†</span>
                                </p>
                                <p class="tac">
                                    <span></span>&nbsp;<span></span>
                                </p>

                            <?php
                            endif; ?>

                            <p class="tac">
                                <?php
                                $player_name  = $order_obj['name'];
                                $player_phone = $order_obj['phone'];

                                echo $player_name;
                                echo "<br>" . $player_phone;
                                echo "<br>" . $order_obj['quantity'] . "Ã— Ù†ÙØ±"; ?>

                                <input type="hidden" class="room_single_operator_sans_player_name" value="<?php echo $player_name ?>">
                                <input type="hidden" class="room_single_operator_sans_player_phone" value="<?php echo $player_phone ?>">
                            </p>
                            <p class="tac">&nbsp</p>
                        </div>
                    </div>

                <?php
                elseif ( $order_obj && isset( $order_obj['status'] ) && (int) $order_obj['status'] === 2): // Ø±Ø²Ø±Ùˆ Ø´Ø¯Ù‡ ØªÙˆØ³Ø· ÛŒÙˆØ²Ø±
                ?>
                    <div class="col-6 col-md-3 res-col res-col res-col-<?php echo $firstTimeTs; ?>">
                        <div class="tac">Ø³Ø§Ø¹Øª
                            <span><?php echo jdate('H:i', $firstTimeTs) ?></span>
                            <br>
                            <p class="tac">ØºÛŒØ± Ù‚Ø§Ø¨Ù„ Ø±Ø²Ø±Ùˆ</p>

                            <p>&nbsp;</p>
                            <input type="button" class="btn btn-success btn-sm btn-block green-bg fff-c"
                                data-start="<?php echo $firstTimeTs ?>" value="Ø¨Ø§Ø²Ú©Ù†" data-pricesel="<?php echo $price; ?>"
                                id="btn_<?php echo $firstTimeTs ?>" data-status="open"
                                data-service="<?php echo $service_id; ?>" onclick="time_click_bazkon(this.id)"
                                <?php
                                echo $firstTimeTs < $auto_disable ? 'disabled' : ''; ?>>
                        </div>
                    </div>

                <?php
                else: ?>
                    <div class="col-6 col-md-3 res-col res-col res-col-<?php echo $firstTimeTs; ?> ghadr">

                        <p class="tac">Ø³Ø§Ø¹Øª
                            <span><?php echo jdate('H:i', $firstTimeTs) ?></span>
                            <br>
                        <p class="tac"> Ù‚Ø§Ø¨Ù„ Ø±Ø²Ø±Ùˆ</p>
                        <p class="tac">&nbsp;</p>

                        <?php
                        if (in_array($firstTimeTs, $bookings)) { ?>
                            <p class="btn btn-success btn-sm btn-block fff-c" style="width: 100%;border: none;border-radius: 4px;background: #ff8d00;cursor: not-allowed;">
                                Ø¯Ø± Ø­Ø§Ù„ Ø±Ø²Ø±Ùˆ</p>

                        <?php
                        } else { // Ù†Ù‡ Ø¨Ø³ØªÙ‡ Ø´Ø¯Ù‡ Ù†Ù‡ Ø±Ø²Ø±Ùˆ Ø´Ø¯Ù‡
                        ?>
                            <input type="button" class="btn btn-success btn-sm btn-block red-bg fff-c"
                                data-start="<?php echo $firstTimeTs ?>" value="Ø­Ø°Ù Ú©Ù†"
                                data-pricesel="<?php echo $price; ?>"
                                id="btn_<?php echo $firstTimeTs ?>" data-status="2"
                                data-service="<?php echo $service_id; ?>" onclick="time_click_hazf(this.id)"
                                <?php
                                echo $firstTimeTs < $auto_disable ? 'disabled' : ''; ?>>
                        <?php
                        } ?>

                        </p>
                    </div>

            <?php
                endif;

            endforeach; ?>

        </div>
    </div>

<?php
}

/********************************************************************************************************************************/
function user_desktop_view($args)
{
    global $conn;

    $time_res     = $args->time_res;
    $service_id   = $args->service_id;
    $user_role    = $args->user_role;
    $day_type     = $args->day_type;
    $bookings     = $args->bookings;
    $sanses       = $args->sanses;
    $auto_disable = $args->auto_disable;
    $discount     = $args->discount;

    if ($user_role == "admin" || $user_role == "shop_manager" || $user_role == "shopist" || $user_role == 'contentist') {
        $auto_disable = 0;
    } ?>

    <div class="container">
        <div class="row">

            <?php
            $sanses_list = [];
            $sans_objs   = [];
            $order_objs  = [];

            foreach ($sanses[$day_type] as $sans) {
                $sanses_list[] = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
            }

            $sanses_list = implode(',', $sanses_list);

            if ($sanses_list !== '') {
                $result    = $conn->query(sprintf("SELECT status, booking_time FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` IN (%s)", $service_id, $sanses_list));

                if ($result->num_rows > 0) {
                    $sans_objs = $result->fetch_all(MYSQLI_ASSOC);
                }
            }

            foreach ($sans_objs as $sans_obj) {
                $order_objs[(string) $sans_obj['booking_time']] = $sans_obj;
            }

            foreach ($sanses[$day_type] as $sans) :

                $firstTimeTs = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
                $price = ! empty($sans['off_price']) ? $sans['off_price'] : $sans['price'];

                $order_obj = isset( $order_objs[ $firstTimeTs ] ) ? $order_objs[ $firstTimeTs ] : null;

                $t1 = strtotime(date("Y-m-d", $firstTimeTs) . ' 00:00');
                $t2 = strtotime(date("Y-m-d", $firstTimeTs) . ' 08:00');
                if (! ($t1 < $firstTimeTs && $t2 > $firstTimeTs)) :
                    if ($firstTimeTs >= $auto_disable) : ?>

                        <div class="col-4 col-md-3 res-col 2">
                            <p class="tac">Ø³Ø§Ø¹Øª
                                <span><?php echo jdate('H:i', $firstTimeTs); ?></span>
                                <br>

                                <?php
                                if ($discount) {

                                    if (! empty($sans['off_price'])) : ?>
                            <p class="tac">Ù†ÙØ±ÛŒ
                                <span class='green-c'><?php echo number_format($sans['off_price'] * (1 - $discount / 100)); ?></span>
                                <span> ØªÙˆÙ…Ø§Ù†</span>
                            </p>
                            <p class="tac">
                                <span>
                                    <del><?php echo number_format($sans['price']); ?></del>
                                </span>
                                <span> ØªÙˆÙ…Ø§Ù†</span>
                            </p>

                        <?php
                                    else: ?>
                            <p class="tac">Ù†ÙØ±ÛŒ
                                <span class='green-c'><?php echo number_format($sans['price'] * (1 - $discount / 100)); ?></span>
                                <span> ØªÙˆÙ…Ø§Ù†</span>
                            </p>
                            <p class="tac">
                                <span>
                                    <del><?php echo number_format($sans['price']); ?></del>
                                </span>
                                <span> ØªÙˆÙ…Ø§Ù†</span>
                            </p>
                        <?php
                                    endif; ?>

                        <?php
                                } else {
                                    if (! empty($sans['off_price'])) : ?>
                            <p class="tac">Ù†ÙØ±ÛŒ
                                <span class='green-c'><?php echo number_format($sans['off_price']); ?></span>
                                <span> ØªÙˆÙ…Ø§Ù†</span>
                            </p>
                            <p class="tac">
                                <span>
                                    <del><?php echo number_format($sans['price']); ?></del>
                                </span>
                                <span> ØªÙˆÙ…Ø§Ù†</span>
                            </p>

                        <?php
                                    else: ?>
                            <p class="tac">Ù†ÙØ±ÛŒ
                                <span class='green-c'><?php echo number_format($sans['price']); ?></span>
                                <span> ØªÙˆÙ…Ø§Ù†</span>
                            </p>
                            <p class="tac">
                                <span></span>&nbsp;<span></span>
                            </p>
                    <?php
                                    endif;
                                } ?>
                    </p>

                    <?php
                        if ( $order_obj && isset( $order_obj['status'] ) && (int) $order_obj['status'] === 1 ) : ?>
                        <p class="btn btn-success btn-sm btn-block fff-c" style="width: 100%;border: none;border-radius: 4px;background: #c00000;cursor: not-allowed;">
                            Ø±Ø²Ø±Ùˆ Ø´Ø¯Ù‡</p>

                    <?php
                        elseif ( $order_obj && isset( $order_obj['status'] ) && (int) $order_obj['status'] === 2 ) : ?>
                        <p class="btn btn-success btn-sm btn-block fff-c" style="width: 100%;border: none;border-radius: 4px;background: #4e4e4e;cursor: not-allowed;">
                            ØºÛŒØ±Ù‚Ø§Ø¨Ù„ Ø±Ø²Ø±Ùˆ</p>

                    <?php
                        elseif (in_array($firstTimeTs, $bookings)) : ?>
                        <p class="btn btn-success btn-sm btn-block fff-c" style="width: 100%;border: none;border-radius: 4px;background: #ff8d00;cursor: not-allowed;">
                            Ø¯Ø± Ø­Ø§Ù„ Ø±Ø²Ø±Ùˆ</p>

                    <?php
                        else : ?>
                        <input type="button" class="btn btn-success btn-sm btn-block green-bg fff-c"
                            data-start="<?php echo $firstTimeTs; ?>"
                            value="Ù‚Ø§Ø¨Ù„ Ø±Ø²Ø±Ùˆ"
                            data-pricesel="<?php echo $price; ?>"
                            id="btn_<?php echo $firstTimeTs; ?>"
                            data-status="open"
                            data-service="<?php echo $service_id; ?>"
                            onclick="time_click(this.id)">
                    <?php
                        endif; ?>
                        </div>

            <?php
                    endif;
                endif;
            endforeach; ?>
        </div>
    </div>

<?php
}

/********************************************************************************************************************************/
function user_mobile_view($args)
{
    global $conn;

    $time_res     = $args->time_res;
    $service_id   = $args->service_id;
    $user_role    = $args->user_role;
    $day_type     = $args->day_type;
    $bookings     = $args->bookings;
    $sanses       = $args->sanses;
    $auto_disable = $args->auto_disable;
    $discount     = $args->discount;

    if ($user_role == "admin" || $user_role == "shop_manager" || $user_role == "shopist" || $user_role == 'contentist') {
        $auto_disable = 0;
    } ?>

    <div class="container">
        <div class="row">

            <?php
            $sanses_list = [];
            $sans_objs   = [];
            $order_objs  = [];

            foreach ($sanses[$day_type] as $sans) {
                $sanses_list[] = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
            }

            $sanses_list = implode(',', $sanses_list);

            if ($sanses_list !== '') {
                $result    = $conn->query(sprintf("SELECT status, booking_time FROM wp_zb_booking_history WHERE room_id LIKE %s AND `booking_time` IN (%s)", $service_id, $sanses_list));

                if ($result->num_rows > 0) {
                    $sans_objs = $result->fetch_all(MYSQLI_ASSOC);
                }
            }

            foreach ($sans_objs as $sans_obj) {
                $order_objs[(string) $sans_obj['booking_time']] = $sans_obj;
            }

            foreach ($sanses[$day_type] as $sans) :

                $firstTimeTs = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);
                $price = ! empty($sans['off_price']) ? $sans['off_price'] : $sans['price'];

                $order_obj = isset( $order_objs[ $firstTimeTs ] ) ? $order_objs[ $firstTimeTs ] : null;

                $t1 = strtotime(date("Y-m-d", $firstTimeTs) . ' 00:00');
                $t2 = strtotime(date("Y-m-d", $firstTimeTs) . ' 08:00');
                if (! ($t1 < $firstTimeTs && $t2 > $firstTimeTs)) :
                    if ($firstTimeTs >= $auto_disable) : ?>

                        <div class="col-6 col-md-3 res-col ">

                            <p class="tac">Ø³Ø§Ø¹Øª
                                <span><?php echo jdate('H:i', $firstTimeTs); ?></span>
                                <br>

                                <?php
                                if ($discount) {

                                    if (! empty($sans['off_price'])) : ?>
                            <p class="tac">Ù†ÙØ±ÛŒ
                                <span class='green-c'><?php echo number_format($sans['off_price'] * (1 - $discount / 100)); ?></span>
                                <span> ØªÙˆÙ…Ø§Ù†</span>
                            </p>
                            <p class="tac">
                                <span>
                                    <del><?php echo number_format($sans['price']); ?></del>
                                </span>
                                <span> ØªÙˆÙ…Ø§Ù†</span>
                            </p>

                        <?php
                                    else: ?>
                            <p class="tac">Ù†ÙØ±ÛŒ
                                <span class='green-c'><?php echo number_format($sans['price'] * (1 - $discount / 100)); ?></span>
                                <span> ØªÙˆÙ…Ø§Ù†</span>
                            </p>
                            <p class="tac">
                                <span>
                                    <del><?php echo number_format($sans['price']); ?></del>
                                </span>
                                <span> ØªÙˆÙ…Ø§Ù†</span>
                            </p>

                        <?php
                                    endif; ?>

                        <?php
                                } else {
                                    if (! empty($sans['off_price'])) : ?>
                            <p class="tac">Ù†ÙØ±ÛŒ
                                <span class='green-c'><?php echo number_format($sans['off_price']); ?></span>
                                <span> ØªÙˆÙ…Ø§Ù†</span>
                            </p>
                            <p class="tac">
                                <span>
                                    <del><?php echo number_format($sans['price']); ?></del>
                                </span>
                                <span> ØªÙˆÙ…Ø§Ù†</span>
                            </p>

                        <?php
                                    else: ?>
                            <p class="tac">Ù†ÙØ±ÛŒ
                                <span class='green-c'><?php echo number_format($sans['price']); ?></span>
                                <span> ØªÙˆÙ…Ø§Ù†</span>
                            </p>
                            <p class="tac">
                                <span></span>&nbsp;<span></span>
                            </p>

                    <?php
                                    endif;
                                } ?>
                    </p>

                    <?php
                        if ( $order_obj && isset( $order_obj['status'] ) && (int) $order_obj['status'] === 1 ) : ?>
                        <p class="btn btn-success btn-sm btn-block fff-c" style="width: 100%;border: none;border-radius: 4px;background: #c00000;cursor: not-allowed;">
                            Ø±Ø²Ø±Ùˆ Ø´Ø¯Ù‡</p>

                    <?php
                        elseif ( $order_obj && isset( $order_obj['status'] ) && (int) $order_obj['status'] === 2 ) : ?>
                        <p class="btn btn-success btn-sm btn-block fff-c" style="width: 100%;border: none;border-radius: 4px;background: #4e4e4e;cursor: not-allowed;">
                            ØºÛŒØ±Ù‚Ø§Ø¨Ù„ Ø±Ø²Ø±Ùˆ</p>

                    <?php
                        elseif (in_array($firstTimeTs, $bookings)) : ?>
                        <p class="btn btn-success btn-sm btn-block fff-c" style="width: 100%;border: none;border-radius: 4px;background: #ff8d00;cursor: not-allowed;">
                            Ø¯Ø± Ø­Ø§Ù„ Ø±Ø²Ø±Ùˆ</p>

                    <?php
                        else : ?>
                        <input type="button" class="btn btn-success btn-sm btn-block green-bg fff-c"
                            data-start="<?php echo $firstTimeTs; ?>"
                            value="Ù‚Ø§Ø¨Ù„ Ø±Ø²Ø±Ùˆ"
                            data-pricesel="<?php echo $price; ?>"
                            id="btn_<?php echo $firstTimeTs; ?>"
                            data-status="open"
                            data-service="<?php echo $service_id; ?>"
                            onclick="time_click(this.id)">
                    <?php
                        endif; ?>

                        </div>

            <?php
                    endif;
                endif;
            endforeach; ?>
        </div>
    </div>

<?php
}

/********************************************************************************************************************************/
function get_sans_lock($product_id)
{
    global $conn;

    $result = $conn->query("SELECT * FROM booking_lock_schedule WHERE product_id LIKE " . "'" . $product_id . "'");
    if ($result->num_rows > 0) {
        $product_obj = $result->fetch_all(MYSQLI_ASSOC);
    }

    return [];

    return $product_obj;
}

/********************************************************************************************************************************/
function remove_sans_lock($product_id, $booking_time)
{
    global $conn;

    $conn->query("DELETE FROM booking_lock_schedule WHERE product_id Like {$product_id} AND booking_time Like {$booking_time}");

    return true;
}
