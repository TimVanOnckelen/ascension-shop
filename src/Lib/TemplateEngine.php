<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 22/05/2019
 * Time: 17:35
 */

namespace AscensionShop\Lib;


class TemplateEngine
{

    protected $vars = array();
    protected $template_dir = XE_ASCENSION_SHOP_PLUGIN_TEMPLATE_PATH;


    /**
     * TemplateEngine constructor.
     *
     * @param null $template_dir
     */
    public function __construct($template_dir = null)
    {
        if ($template_dir !== null) {
            // Check here whether this directory really exists
            $this->template_dir = $template_dir;
        }
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->vars[$name];
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->vars[$name] = $value;
    }

    /**
     * @param $template_file
     *
     * @return string
     * @throws \Exception
     */
    public function display($template_file)
    {

        if (file_exists($this->template_dir . $template_file)) {

            // Display the template
            ob_start();
            include $this->template_dir . $template_file;
            return ob_get_clean();

        } else {
            throw new \Exception('no template file ' . $template_file . ' present in directory ' . $this->template_dir);
        }


    }

}