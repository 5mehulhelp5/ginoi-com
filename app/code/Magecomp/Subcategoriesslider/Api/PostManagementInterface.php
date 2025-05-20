<?php
namespace Magecomp\Subcategoriesslider\Api;
interface PostManagementInterface {
    /**
     * POST api for Subcategorieslider
     * @param string $maincategoryid
     * @param string $subcategoryid
     * @param string $storeid
     * @return string
     */

    public function setSubcategorySlider($maincategoryid,$subcategoryid,$storeid);

    /**
     * POST api for Subcategorielist
     * @param string $maincategorylistid
     * @param string $subcategorylistid
     * @param string $storeid
     * @return string
     */

    public function setSubcategoryList($maincategorylistid,$subcategorylistid,$storeid);

     /**
     * Get Configuration data
     * @param int $storeid
     * @return string
     */

   public function getConfigData($storeid);
}
