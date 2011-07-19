<?php

class MapDBDataParser extends DataParser implements MapDataParser
{
    private $category = null;
    private $categoryId;

    // MapDataParser interface

    public function getProjection()
    {
        return $this->getCategory()->getProjection();
    }

    public function getListItems()
    {
        return array_merge($this->getChildCategories(), $this->getAllFeatures());
    }

    public function getAllFeatures()
    {
        return $this->db->featuresForCategory($this->categoryId);
    }

    public function getChildCategories()
    {
        return $this->db->childrenForCategory($this->categoryId);
    }

    // overrides

    public function init($args) {
        parent::init($args);
        $this->categoryId = mapIdForFeedData($args);
    }

    public function parseData($data) {
        // do nothing
    }

    // everything else

    public function isStored() {
        return $this->getCategory()->isStored();
    }

    public function getCategoryId()
    {
        return $this->categoryId;
    }

    public function getChild($childId) {
        return self::getChildForCategory($childId, $this->categoryId);
    }

    public function getCategory() {
        if (!$this->category) {
            $this->category = MapDB::categoryForId($this->categoryId);
        }
        return $this->category;
    }

    public function getFeatureById($featureId)
    {
        return MapDB::getFeatureByIdAndCategory($featureId, $this->categoryId);
    }
}
