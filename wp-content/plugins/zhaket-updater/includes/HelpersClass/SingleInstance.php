<?php


namespace ZhaketUpdater\HelpersClass;


trait SingleInstance
{
    private static $instance = null;

    public static function getInstance(...$args)
    {
        if (isset(self::$PreventCron) && defined( 'DOING_CRON' ) && DOING_CRON){
            return ;
        }
        if ( null === self::$instance ) {
            self::$instance = new self(...$args);
        }
        return self::$instance;
    }
}