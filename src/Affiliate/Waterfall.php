<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 2/08/2019
 * Time: 22:00
 */

namespace AscensionShop\Affiliate;


class Waterfall
{

    private $waterfallArray = array();
    private static $tempWaterfall = array();

    public function __construct()
    {

        add_action("admin_menu", array($this, "addConsolePage"));
        add_action('admin_enqueue_scripts', array($this, "addJs"));
        add_action('wp_enqueue_scripts', array($this, "addJs"));

        // Add waterfall to order
        add_action('add_meta_boxes', array($this, 'addMetaBoxes'));

        // Add watefall to affiliate box
        add_filter('affwp_affiliate_area_tabs', array($this, "addWaterfallTab"));
        add_filter('affwp_template_paths', array($this, "addCustomTemplateFolder"));
    }

    public function addJs()
    {

        if (is_admin() && $_GET["page"] != "ascension-shop-waterfall") {
            return;
        }

        wp_enqueue_script('orgchart', XE_ASCENSION_SHOP_PLUGIN_DIR . 'assets/js/orgchart.js');
    }

    /**
     * Add the console page to admin
     */
    public function addConsolePage()
    {
        add_submenu_page(
            'affiliate-wp',
            'Waterfall',
            'Waterfall',
            'manage_options',
            'ascension-shop-waterfall',
            array($this, 'viewWaterfall')
        );
    }

    public function viewWaterfall()
    {

        $theWaterfall = $this->getFullWaterfall(false);
        // Add custom css
        $inactive_members = array_keys(array_combine(array_keys($theWaterfall), array_column($theWaterfall, 'status')), "");
        // Add custom css for inactive members
        $this->generateInactiveCss($inactive_members, $theWaterfall);
        ?>
        <div style="width:100%; height:700px;" id="orgchart"></div>
        <script>
            var chart = new OrgChart(document.getElementById("orgchart"), {
                template: "polina",
                nodeBinding: {
                    field_0: "name",
                    field_1: "level",
                    field_2: "rate",
                    field_3: "ref_id"
                },
                nodes: <?php echo json_encode($theWaterfall); ?>
            });
        </script>
        <?php
    }

    /**
     * @param $nodes
     * @param $theWaterfall
     */
    private function generateInactiveCss($nodes, $theWaterfall)
    {
        ?>
        <style type="text/css">
            <?php
             foreach($nodes as $n){
                 ?>
            [node-id='<?php echo $theWaterfall[$n]["id"]; ?>'] rect {
                fill: grey;
            }

            <?php
             }
            ?>
        </style>

        <?php
    }

    // Gets a full waterfall of all affiliates
    public function getFullWaterfall($json = true)
    {

        $levelZeros = Helpers::getAllAffiliatesWithLevelZero();

        foreach ($levelZeros as $u) {

            // Get name
            $affiliate_name = affiliate_wp()->affiliates->get_affiliate_name($u->affiliate_id);
            $sub = new SubAffiliate($u->affiliate_id);

            // Add to waterfall
            $this->waterfallArray[] = array("id" => $u->affiliate_id, "name" => $affiliate_name, "level" => "Level 0", "ref_id" => $u->affiliate_id, "rate" => $sub->getUserRate(), "status" => $sub->getStatus());

            $children = Helpers::getAllChilderen($u->affiliate_id);
            // Get all children
            if ($children != false) {
                $this->getWaterfall($children, $u->affiliate_id);
            }
        }

        if ($json == true) {
            return json_encode($this->waterfallArray);
        } else {
            return $this->waterfallArray;
        }
    }

    /**
     * @param $children
     * @param $parent_id
     *
     */
    public function getWaterfall($children, $parent_id)
    {

        foreach ($children as $c) {

            $sub = new SubAffiliate($c->affiliate_id);
            $affiliate_name = affiliate_wp()->affiliates->get_affiliate_name($c->affiliate_id);

            // Add to waterfall
            $temp = array("ref_id" => $c->affiliate_id, "id" => $c->affiliate_id, "name" => $affiliate_name, "pid" => $parent_id, "level" => "Level " . $sub->getLevel(), "rate" => $sub->getUserRate(), "status" => $sub->getStatus());
            $this->waterfallArray[] = $temp;

            // Do untill there are no children anymore
            $children = Helpers::getAllChilderen($c->affiliate_id);
            if ($children != false) {
                $this->getWaterfall($children, $c->affiliate_id);
            }

        }


    }

    public function addMetaBoxes()
    {
        add_meta_box(
            'xe_ascension_',      // Unique ID
            esc_html__('Referral waterfall', 'ascension-shop'),    // Title
            array($this, 'addOrderWaterfall'),   // Callback function
            'shop_order',         // Admin page (or post type)
            'normal',         // Context
            'default'         // Priority
        );
    }

    /**
     * Get the order waterfall
     */
    public function addOrderWaterfall()
    {

        $order_id = get_the_ID();
        $waterfall = Helpers::getReferralsFromOrder($order_id, "woocommerce");
        $full_waterfall = array();

        // Loop over watefall, if there is any
        if (isset($waterfall)) {
            foreach ($waterfall as $ref) {
                $sub = new SubAffiliate($ref->affiliate_id);
                $tempWaterfall = array();
                $tempWaterfall["id"] = $sub->getId();
                $tempWaterfall["pid"] = $sub->getParentId();
                $tempWaterfall["amount"] = $ref->amount . ' euro';
                $tempWaterfall["name"] = affiliate_wp()->affiliates->get_affiliate_name($sub->getId());
                $full_waterfall[] = $tempWaterfall;
            }
        }

        // Show the waterfall :)
        self::waterFallTemplate($full_waterfall);

    }

    /**
     * @param $nodes
     */
    private static function waterFallTemplate($nodes)
    {
        ?>
        <div style="width:100%; height:700px;" id="orgchart"></div>
        <script>
            var chart = new OrgChart(document.getElementById("orgchart"), {
                template: "polina",
                nodeBinding: {
                    field_0: "name",
                    field_1: "amount",
                    field_2: "id"
                },
                nodes: <?php echo json_encode($nodes); ?>
            });
        </script>
        <?php
    }

    /**
     * @param $nodes
     */
    private static function waterFallFrontendTemplate($nodes)
    {
        ?>
        <div style="width:100%; height:700px;" id="orgchart"></div>
        <script>
            var chart = new OrgChart(document.getElementById("orgchart"), {
                template: "polina",
                nodeBinding: {
                    field_0: "name",
                    field_1: "rate",
                    field_2: "id"
                },
                nodes: <?php echo json_encode($nodes); ?>
            });
        </script>
        <?php
    }

    /**
     * @param $tabs
     * @return mixed
     */
    public function addWaterfallTab($tabs)
    {
        $tabs["waterfall"] = __("Affiliate waterval", "ascension-shop");

        return $tabs;
    }

    /**
     * @param $paths
     * @return array
     */
    public function addCustomTemplateFolder($paths)
    {
        $paths[] = XE_ASCENSION_SHOP_PLUGIN_TEMPLATE_PATH . 'affiliate-wp/';
        return $paths;
    }

    /**
     * @param $affiliate_id
     */
    public static function getWaterfallFromUser($affiliate_id)
    {

        $waterfall = Helpers::getAllChilderen($affiliate_id);
        $full_waterfall = array();
        // Loop over waterfall, if there is any

        if (isset($waterfall) && !empty($waterfall)) {

            // Add self
            $sub = new SubAffiliate($affiliate_id);
            $tempWaterfall = array();
            $tempWaterfall["id"] = $sub->getId();
            $tempWaterfall["pid"] = $sub->getParentId();
            $tempWaterfall["rate"] = $sub->getUserRate() . '%';
            $tempWaterfall["name"] = affiliate_wp()->affiliates->get_affiliate_name($sub->getId());
            self::$tempWaterfall[] = $tempWaterfall;

            // Loop over
            self::loopOverChildren($waterfall, $affiliate_id);

            self::waterFallFrontendTemplate(self::$tempWaterfall);
        } else {
            _e("Je hebt geen partner affiliates onder jouw hangen.", "ascension-shop");
        }

    }

    /**
     * @param $children
     * @param $parent_id
     */
    private static function loopOverChildren($children, $parent_id)
    {

        foreach ($children as $c) {

            $sub = new SubAffiliate($c->affiliate_id);
            $affiliate_name = affiliate_wp()->affiliates->get_affiliate_name($c->affiliate_id);

            // Add to waterfall
            $temp = array("ref_id" => $c->affiliate_id, "id" => $c->affiliate_id, "name" => $affiliate_name, "pid" => $parent_id, "level" => "Level " . $sub->getLevel(), "rate" => $sub->getUserRate(), "status" => $sub->getStatus());
            self::$tempWaterfall[] = $temp;

            // Do untill there are no children anymore
            $children = Helpers::getAllChilderen($c->affiliate_id);
            if ($children != false) {
                self::loopOverChildren($children, $c->affiliate_id);
            }

        }

    }
}