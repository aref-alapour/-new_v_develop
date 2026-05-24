jQuery(document).ready(function($) {
    // Handle copy shortlink button clicks
    $('.ez-copy-shortlink').on('click', function(e) {
        e.preventDefault();
        
        var targetId = $(this).data('target');
        var input = $('#' + targetId);
        var textToCopy = input.val();
        
        if (!textToCopy) {
            alert('لینک کوتاه موجود نیست!');
            return;
        }
        
        var $button = $(this);
        var originalText = $button.text();
        
        // Method 1: Try modern Clipboard API first
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(textToCopy).then(function() {
                showSuccessMessage($button, originalText);
            }).catch(function(err) {
                console.error('Clipboard API failed:', err);
                // Fallback to execCommand
                fallbackCopy(textToCopy, $button, originalText);
            });
        } else {
            // Method 2: Fallback to execCommand
            fallbackCopy(textToCopy, $button, originalText);
        }
    });
    
    // Fallback copy method using execCommand
    function fallbackCopy(text, $button, originalText) {
        // Create a temporary textarea element
        var textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.left = '-999999px';
        textarea.style.top = '-999999px';
        document.body.appendChild(textarea);
        
        // Select and copy
        textarea.focus();
        textarea.select();
        
        try {
            var successful = document.execCommand('copy');
            if (successful) {
                showSuccessMessage($button, originalText);
            } else {
                // Method 3: Last resort - prompt user to copy manually
                manualCopyPrompt(text, $button, originalText);
            }
        } catch (err) {
            console.error('execCommand failed:', err);
            manualCopyPrompt(text, $button, originalText);
        }
        
        // Clean up
        document.body.removeChild(textarea);
    }
    
    // Manual copy prompt as last resort
    function manualCopyPrompt(text, $button, originalText) {
        // Create a temporary input field
        var tempInput = document.createElement('input');
        tempInput.value = text;
        tempInput.style.position = 'fixed';
        tempInput.style.left = '-999999px';
        tempInput.style.top = '-999999px';
        document.body.appendChild(tempInput);
        
        // Select the text
        tempInput.focus();
        tempInput.select();
        
        // Show alert with instructions
        alert('لینک انتخاب شد. لطفاً Ctrl+C (یا Cmd+C در مک) را فشار دهید تا کپی شود.');
        
        // Clean up
        document.body.removeChild(tempInput);
        
        // Show success message anyway
        showSuccessMessage($button, originalText);
    }
    
    // Show success message
    function showSuccessMessage($button, originalText) {
        $button.text('کپی شد!').addClass('button-primary');
        
        // Reset button after 2 seconds
        setTimeout(function() {
            $button.text(originalText).removeClass('button-primary');
        }, 2000);
    }
}); 