<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 28/05/2019
 * Time: 16:06
 */

namespace AscensionShop\Lib;


class MessageHandeling
{

    private static $key_message = "ascension-shop_success_m";
    private static $key_normal_message = "ascension-shop_m";
    private static $key_error_message = "ascension-shop_error_m";

    public function __construct()
    {

        // Add success message to top
        add_filter('the_content', array($this, 'frontendSuccesMessage'), 99, 1);

        // Add Normal message to top
        add_filter('the_content', array($this, 'frontendMessage'), 99, 1);

        // Add errors to top
        add_filter('the_content', array($this, 'showErrors'), 99, 1);

        add_action("wp_enqueue_scripts", array($this, "addCss"));

        add_action("wp_footer", array($this, "clearErrors"));
    }

    public function addCss()
    {
        wp_enqueue_style("message-style", XE_ASCENSION_SHOP_PLUGIN_DIR . "assets/css/messages.min.css");
    }

    /**
     * @param $content
     *
     * @return mixed
     */
    public function frontendMessage($content)
    {

        // Get messages
        $messages = get_transient(self::$key_normal_message . wp_get_session_token());

        // Show frontend message
        if (!empty($messages)) {

            // Delete all errors, now that they have been shown
            delete_transient(self::$key_normal_message . wp_get_session_token());
        }

        return $content;

    }

    /**
     * Render a frontend succes Message
     * @param $content
     *
     * @return string
     */
    public function frontendSuccesMessage($content)
    {

        // Get messages
        $messages = get_transient(self::$key_message . wp_get_session_token());
        $message = '';

        // Only show if no Errors
        if (!empty($messages) && empty($errors)) {

            // Init template engine
            $t = new TemplateEngine();

            // No array of errors, but one
            if (!is_array($messages)) {

                // Try to display error
                try {
                    // set error
                    $t->e = $messages;
                    $message = $t->display("messages/success.php");
                } catch (\Exception $er) {
                    return $er;
                }

            } else {

                $message = '';

                // Show each error
                foreach ($messages as $e) {

                    // Try to display error
                    try {
                        // set error
                        $t->e = $e;
                        $message .= $t->display("messages/success.php");

                    } catch (\Exception $er) {
                        return $er;
                    }

                }
            }


        }

        return $message . $content;
    }

    /**
     * Add errors to content
     * @param $content
     * @return \Exception|string
     */
    public function showErrors($content)
    {

        // Get errors
        $errors = get_transient(self::$key_error_message . wp_get_session_token());


        // Check if there are errors
        if (!empty($errors)) {

            // Init template engine
            $t = new TemplateEngine();

            // No array of errors, but one
            if (!is_array($errors)) {
                // Try to display error
                try {
                    // set error
                    $t->e = $errors;
                    $message = $t->display("messages/error.php");

                    return $message . $content;

                } catch (\Exception $er) {
                    return $er;
                }

            } else {
                $message = "";

                // Show each error
                foreach ($errors as $e) {

                    // Try to display error
                    try {
                        // set error
                        $t->e = $e;
                        $message .= $t->display("messages/error.php");

                    } catch (\Exception $er) {
                        return $er;
                    }

                }
            }


            return $message . $content;
        }

        // Delete all errors, now that they have been shown

        return $content;

    }

    /**
     * @return mixed
     */
    public static function getErrors()
    {
        // Get errors
        $errors = get_transient(self::$key_error_message . wp_get_session_token());

        return $errors;
    }

    public function getSucces()
    {
        // Get errors
        $success = get_transient(self::$key_message . wp_get_session_token());

        return $success;
    }

    /**
     * @param $m
     * @param bool $type
     */
    public static function setMessage($m, $type = false)
    {

        switch ($type) {
            case "succes";
                // Set a success transient for this message
                set_transient(self::$key_message . wp_get_session_token(), $m);
                break;

            case "error";
                // Set a success transient for this message
                set_transient(self::$key_error_message . wp_get_session_token(), $m);
                break;

            default;
                // Set a normal transient for this message
                set_transient(self::$key_message . wp_get_session_token(), $m);
                break;
        }


    }

    public function clearErrors()
    {

        delete_transient(self::$key_error_message . wp_get_session_token());
        // Delete all errors, now that they have been shown
        delete_transient(self::$key_message . wp_get_session_token());
    }

}