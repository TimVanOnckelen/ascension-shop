<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 2/08/2019
 * Time: 12:01
 */

namespace AscensionShop\Affiliate;


class SubAffiliate
{

    private $level;
    private $affiliate_id;
    private $parent_id;
    private $rate;
    private $children_array = array();

    public function __construct($id)
    {

        $this->affiliate_id = $id;

    }

    public function getId()
    {
        return $this->affiliate_id;
    }

    public function getUserId(){
	    return affwp_get_affiliate_user_id($this->getId());
    }

    public function getName(){
    	return affwp_get_affiliate_name($this->getId());
    }

    public function getEmail(){
    	return affwp_get_affiliate_email($this->getId());
    }
    /**
     * Check if user is a sub aff
     * @return bool
     */
    public function isSub()
    {
        if ($this->getParentId() == '' OR $this->getParentId() == null) {
            return false;
        }
        // User is sub
        return true;
    }

    /**
     * Get Parent id
     * @return mixed
     */
    public function getParentId()
    {

        $this->parent_id = affwp_get_affiliate_meta($this->affiliate_id, "ascension_parent_id", true);

        return $this->parent_id;

    }

    /**
     * Parent of the current affiliate
     * @return mixed
     */
    public function getParentUser()
    {

        return affwp_get_affiliate($this->parent_id);

    }

    /**
     * The user level in matrix
     * @param bool $id
     * @return mixed
     */
    public function getLevel($id = false)
    {

        if ($id == false) {
            $id = $this->affiliate_id;
        }

        $this->level = affwp_get_affiliate_meta($id, "ascension_aff_level", true);
        if ($this->level == '' OR $this->level == null) {
            $this->level = 0;
        }
        return $this->level;
    }


    /**
     * Status of affiliate
     * @return mixed
     */
    public function getStatus()
    {
        return affwp_is_active_affiliate($this->getId());
    }

    /**
     * Get the user rate
     * @return bool|int
     */
    public function getUserRate()
    {

        $other_rate = $this->hasOtherRate();

        if ($other_rate != false && $other_rate > 0) {
            $rate = $other_rate;
        } else {
            // The user rate based on level
            $rate = RateLevelsInit::getLevelRate($this->getLevel());
        }

        return $rate;

    }

    /**
     * @param $id
     */
    public function saveParent($id)
    {
        affwp_update_affiliate_meta($this->affiliate_id, "ascension_parent_id", $id);

        // update all levels
        Helpers::updateLevels($id, $this->affiliate_id);

    }

    /**
     * @param $rate
     */
    public function saveCustomRate($rate)
    {
        affwp_update_affiliate_meta($this->affiliate_id, "ascension_custom_rate", $rate);

    }

    /**
     * Get the full waterfall of parents
     * @return array
     */
    public function getFullParentWaterfall()
    {

        $parent_array = array();
        $exit_loop = false;
        $next_child = $this->getId();

        // Loop over parents
        while ($exit_loop == false) {

            $child = new SubAffiliate($next_child);
            $level = $child->getLevel();

            // Add yourself to waterfall
            $parent_array[$level] = array();
            $parent_array[$level]["id"] = $child->getId();
            $parent_array[$level]["level"] = $level;

            // No parent anymore, so exit
            if ($child->getParentId() == false) {
                $exit_loop = true;
            } else {
                // Else start over with current parent
                $next_child = $child->getParentId();
            }

        }

        ksort($parent_array);

        return $parent_array;
    }

    /**
     * Check if user has a custom rate
     * @return bool
     */
    private function hasOtherRate()
    {

        $this->rate = affwp_get_affiliate_rate($this->affiliate_id);
        $this->rate = $this->rate * 100;

        if ($this->rate > 0) {
            return $this->rate;
        }

        return false;

    }

    public function getCustomRate()
    {

        $rate = $this->hasOtherRate();
        if ($rate != false) {
            return $rate;
        }

        return 0;

    }

    public function getAllChildren(){
	    $children = Helpers::getAllChilderen($this->affiliate_id);
	    $this->loopOverChildren($children,$this->affiliate_id);

	    return $this->children_array;
    }

	/**
	 * @param $children
	 * @param $parent_id
	 */
	private function loopOverChildren($children, $parent_id)
	{

		foreach ($children as $c) {

			$sub = new SubAffiliate($c->affiliate_id);
			$affiliate_name = affiliate_wp()->affiliates->get_affiliate_name($c->affiliate_id);

			// Add to waterfall
			$this->children_array[] = $sub;

			// Do untill there are no children anymore
			$children = Helpers::getAllChilderen($c->affiliate_id);
			if ($children != false) {
				self::loopOverChildren($children, $c->affiliate_id);
			}

		}

	}

}