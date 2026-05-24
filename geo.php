<?php
$geo = explode(',', $_GET['g']);
echo $user_agent = $_SERVER['HTTP_USER_AGENT'];

$is_mobile = false;
if (strpos($user_agent, 'iPhone') !== false || strpos($user_agent, 'iOS') !== false || strpos($user_agent, 'Macintosh') !== false) {
    $is_mobile = true; ?>
    <a href="maps:q=<?php echo $geo[0] . ',' . $geo[1]; ?>" id="hiddenLink""></a>
<?php
} elseif (strpos($user_agent, 'Android') !== false) {
    $is_mobile = true; ?>
    <a href="geo:<?php echo $geo[0] . ',' . $geo[1]; ?>" id="hiddenLink""></a>
<?php
}

if ( $is_mobile ) { ?>

    <script>
        function clickHiddenLink() {
            document.getElementById('hiddenLink').click();
        }
        clickHiddenLink();
        window.close();
    </script>

<?php
}