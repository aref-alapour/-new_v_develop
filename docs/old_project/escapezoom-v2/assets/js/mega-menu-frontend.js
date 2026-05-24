/**
 * Mega Menu Frontend JavaScript
 * اسکریپت فرانت‌اند مگامنو
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initMegaMenu();
    });

    function initMegaMenu() {
        // Handle mobile menu toggle
        handleMobileMenu();
        
        // Handle vertical menu toggle
        handleVerticalMenu();
        
        // Handle accessibility
        handleAccessibility();
        
        // Add current menu item classes
        addCurrentMenuItemClasses();
    }

    /**
     * Handle mobile menu behavior
     */
    function handleMobileMenu() {
        // Check if we're on mobile
        if (window.innerWidth <= 768) {
            enableMobileMenu();
        }
        
        // Re-check on window resize
        $(window).on('resize', debounce(function() {
            if (window.innerWidth <= 768) {
                enableMobileMenu();
            } else {
                disableMobileMenu();
            }
        }, 250));
    }

    /**
     * Enable mobile menu functionality
     */
    function enableMobileMenu() {
        // For horizontal menus, make them behave like accordion on mobile
        $('.mega-menu-horizontal .mega-menu-item.has-children > .mega-menu-link').off('click.megamenu').on('click.megamenu', function(e) {
            e.preventDefault();
            
            const $item = $(this).parent();
            const $submenu = $item.find('> .mega-menu-submenu');
            
            // Toggle current item
            $item.toggleClass('open');
            $submenu.slideToggle(300);
            
            // Close other items (optional - comment out for multi-open behavior)
            $item.siblings('.has-children').removeClass('open').find('> .mega-menu-submenu').slideUp(300);
        });
    }

    /**
     * Disable mobile menu functionality
     */
    function disableMobileMenu() {
        $('.mega-menu-horizontal .mega-menu-item.has-children > .mega-menu-link').off('click.megamenu');
        $('.mega-menu-horizontal .mega-menu-item').removeClass('open');
        $('.mega-menu-horizontal .mega-menu-submenu').removeAttr('style');
    }

    /**
     * Handle vertical menu toggle
     */
    function handleVerticalMenu() {
        $('.mega-menu-vertical .mega-menu-item.has-children > .mega-menu-link').on('click', function(e) {
            e.preventDefault();
            
            const $item = $(this).parent();
            const $submenu = $item.find('> .mega-menu-submenu');
            
            // Toggle current item
            $item.toggleClass('open');
            $submenu.slideToggle(300);
            
            // Close other items (optional - comment out for multi-open behavior)
            // $item.siblings('.has-children').removeClass('open').find('> .mega-menu-submenu').slideUp(300);
        });
    }

    /**
     * Handle keyboard navigation and accessibility
     */
    function handleAccessibility() {
        // Add ARIA attributes
        $('.mega-menu-item.has-children').each(function() {
            const $item = $(this);
            const $link = $item.find('> .mega-menu-link');
            const $submenu = $item.find('> .mega-menu-submenu');
            
            const submenuId = 'submenu-' + Math.random().toString(36).substr(2, 9);
            
            $link.attr('aria-haspopup', 'true');
            $link.attr('aria-expanded', 'false');
            $link.attr('aria-controls', submenuId);
            
            $submenu.attr('id', submenuId);
            $submenu.attr('role', 'menu');
        });
        
        // Update ARIA expanded on hover/click
        $('.mega-menu-horizontal .mega-menu-item.has-children').on('mouseenter', function() {
            $(this).find('> .mega-menu-link').attr('aria-expanded', 'true');
        }).on('mouseleave', function() {
            $(this).find('> .mega-menu-link').attr('aria-expanded', 'false');
        });
        
        // Keyboard navigation
        $('.mega-menu-link, .mega-menu-child-link').on('keydown', function(e) {
            const $this = $(this);
            const $item = $this.parent();
            const $menu = $this.closest('ul');
            
            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    if ($item.hasClass('has-children') && !$item.hasClass('open')) {
                        // Open submenu
                        $item.addClass('open');
                        $item.find('> .mega-menu-submenu').slideDown(300);
                        $this.attr('aria-expanded', 'true');
                        // Focus first child
                        $item.find('> .mega-menu-submenu .mega-menu-child-link').first().focus();
                    } else {
                        // Move to next item
                        const $nextItem = $item.next();
                        if ($nextItem.length) {
                            $nextItem.find('> a').focus();
                        }
                    }
                    break;
                    
                case 'ArrowUp':
                    e.preventDefault();
                    const $prevItem = $item.prev();
                    if ($prevItem.length) {
                        $prevItem.find('> a').focus();
                    } else {
                        // If we're in a submenu, go back to parent
                        if ($menu.hasClass('mega-menu-submenu')) {
                            $menu.parent().find('> .mega-menu-link').focus();
                        }
                    }
                    break;
                    
                case 'ArrowRight':
                    if ($('html').attr('dir') === 'rtl') {
                        // In RTL, right arrow goes to parent
                        if ($menu.hasClass('mega-menu-submenu')) {
                            $menu.parent().find('> .mega-menu-link').focus();
                        }
                    } else {
                        // In LTR, right arrow opens submenu
                        if ($item.hasClass('has-children') && !$item.hasClass('open')) {
                            e.preventDefault();
                            $item.addClass('open');
                            $item.find('> .mega-menu-submenu').slideDown(300);
                            $this.attr('aria-expanded', 'true');
                            $item.find('> .mega-menu-submenu .mega-menu-child-link').first().focus();
                        }
                    }
                    break;
                    
                case 'ArrowLeft':
                    if ($('html').attr('dir') === 'rtl') {
                        // In RTL, left arrow opens submenu
                        if ($item.hasClass('has-children') && !$item.hasClass('open')) {
                            e.preventDefault();
                            $item.addClass('open');
                            $item.find('> .mega-menu-submenu').slideDown(300);
                            $this.attr('aria-expanded', 'true');
                            $item.find('> .mega-menu-submenu .mega-menu-child-link').first().focus();
                        }
                    } else {
                        // In LTR, left arrow goes to parent
                        if ($menu.hasClass('mega-menu-submenu')) {
                            e.preventDefault();
                            $menu.parent().find('> .mega-menu-link').focus();
                        }
                    }
                    break;
                    
                case 'Escape':
                    if ($menu.hasClass('mega-menu-submenu')) {
                        e.preventDefault();
                        $menu.slideUp(300);
                        const $parentItem = $menu.parent();
                        $parentItem.removeClass('open');
                        $parentItem.find('> .mega-menu-link').attr('aria-expanded', 'false').focus();
                    }
                    break;
            }
        });
    }

    /**
     * Add current menu item classes based on current URL
     */
    function addCurrentMenuItemClasses() {
        const currentUrl = window.location.href;
        
        $('.mega-menu-link, .mega-menu-child-link').each(function() {
            const $link = $(this);
            const linkUrl = $link.attr('href');
            
            if (linkUrl && currentUrl.indexOf(linkUrl) !== -1 && linkUrl !== '#') {
                $link.parent().addClass('current-menu-item');
                
                // If it's a child, also mark the parent
                if ($link.hasClass('mega-menu-child-link')) {
                    $link.closest('.mega-menu-item').addClass('current-menu-item');
                }
            }
        });
    }

    /**
     * Debounce function for performance
     */
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }

    /**
     * Close submenu when clicking outside
     */
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.mega-menu-container').length) {
            $('.mega-menu-item.open').removeClass('open');
            $('.mega-menu-submenu').slideUp(300);
            $('.mega-menu-link[aria-expanded="true"]').attr('aria-expanded', 'false');
        }
    });

})(jQuery);

