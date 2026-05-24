<div class="zhkup">
<div class=" w-full h-full hub-bg relative px-10 pt-28 pb-5">
    <img class="absolute top-10 right-2 z-10 image-rotate " src="<?php echo ZHAKET_UPDATER_PLUGIN_ASSET_URL ?>/admin/img/network.svg" alt="">

    <div class="bg-[url('../img/box-bg.png')] bg-cover h-[155px] bg-no-repeat w-full p-7 z-50 flex relative rounded-[35px]">
    <img class="absolute -top-20 left-7 " src="<?php echo ZHAKET_UPDATER_PLUGIN_ASSET_URL ?>/admin/img/hub.svg" alt="">

    <p class="text-sm mt-5 text-justify font-semibold">
		<?php esc_html_e('The first and only smart and secure WordPress hub to bypass sanctions and quickly connect to WordPress services','zhaket-updater') ?>
 </p>
 <div class="absolute right-1/2 translate-x-1/2 bottom-2">
 <label class="switch hub-switch peer" for="hub_status"><input
   id="hub_status" value="True"  type="checkbox" />
          <div class="slider round"><p id="toggle-text" class="text-[#9A9B9C] mr-[16px] mt-[5px]">OFF</p></div>
        </label>
 </div>

    </div>
    <img class="m-auto z-50 mt-5" src="<?php echo ZHAKET_UPDATER_PLUGIN_ASSET_URL ?>/admin/img/zhk-logo.svg" alt="">
</div>
</div>
<script>
 document.addEventListener('DOMContentLoaded', function () {
    let checkbox = document.getElementById('hub_status');
     checkbox.checked = <?php echo !$status ?'false':'true' ?>;
     if (checkbox.checked) {
         document.getElementById('toggle-text').innerText=''
	 }
    // Fetch the saved status on load
    // fetchSavedStatus();

    // Handle checkbox change event
    checkbox.addEventListener('change', function () {
        let status = checkbox.checked ? 'true' : 'false';
        saveCheckboxStatus(status);
    });

    function saveCheckboxStatus(status) {
        let toggleText = document.getElementById('toggle-text');
        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'zhaket_hub_save_checkbox_status',
                status: status,
            },
            success: function (response) {
                if (response.status) {
                    checkbox.checked = response.status === true;
                    toggleText.textContent = checkbox.checked ? '' : "OFF";
                }
            },
            error: function (error) {
                console.error('Error saving status:', error);
                toggleText.textContent = checkbox.checked ? '' : "OFF";

            },
        });
    }

    function fetchSavedStatus() {
        const toggleText = document.getElementById('toggle-text');
        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'zhaket_hub_get_checkbox_status',
            },
            success: function (response) {
                if (response.status) {
                    checkbox.checked = response.status === true;
                    toggleText.textContent = checkbox.checked ? '' : "OFF";
                }
            },
            error: function (error) {
                checkbox.checked  = true;
                toggleText.textContent = checkbox.checked ? '' : "OFF";
            },
        });
    }
});
</script>