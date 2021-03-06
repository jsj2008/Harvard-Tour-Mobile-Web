<?php

/****************************************************************
 *
 *  Copyright 2011-2012 The President and Fellows of Harvard College
 *  Copyright 2011-2012 Modo Labs Inc.
 *
 *****************************************************************/

class TourAPIModule extends APIModule {
    protected $id = 'tour';
    
    protected function htmlToText($html) {
        return mb_convert_encoding(strip_tags($html), 'UTF-8', 'HTML-ENTITIES');
    }
    
    protected function getBriefStopDetails($stop) {
        $coords = $stop->getCoords();
        
        $lenses = array();
        foreach ($stop->getAvailableLenses() as $lensKey) {
            $lenses[$lensKey] = array(
                'updated' => $stop->getLensLastUpdate($lensKey),
            );
        }
        
        return array(
            'details' => array(
                'id'        => $stop->getId(),
                'title'     => $this->htmlToText($stop->getTitle()),
                'subtitle'  => $this->htmlToText($stop->getSubtitle()),
                'photo'     => $stop->getPhotoSrc(),
                'thumbnail' => $stop->getThumbnailSrc(),
                'lat'       => $coords['lat'],
                'lon'       => $coords['lon'],
                'updated'   => $stop->getLastUpdate(),
            ),
            'lenses' => $lenses,
        );
    }
    
    protected function formatLensContent($content) {
        switch (get_class($content)) {
            case 'TourText':
                return array(
                    'type' => 'html',
                    'html' =>  $content->getContent(),
                );
                
            case 'TourPhoto':
                return array(
                    'type'  => 'photo',
                    'url'   => $content->getSrc(),
                    'title' => $this->htmlToText($content->getTitle()),
                );
                
            case 'TourVideo':
                return array(
                    'type'  => 'video',
                    'url'   => $content->getSrc(),
                    'title' => $this->htmlToText($content->getTitle()),
                );
                
            case 'TourSlideshow':
                $slides = array();
                foreach ($content->getSlides() as $slideContent) {
                    $slides[] = $this->formatLensContent($slideContent);
                }
                return array(
                    'type' => 'slideshow',
                    'slides' => $slides,
                );
        }
        return null;
    }
    
    protected function getStopDetails($stop) {
        $stopDetails = $this->getBriefStopDetails($stop);
        
        $lenses = $stop->getAvailableLenses();
        foreach ($stopDetails['lenses'] as $lensKey => $contents) {
            $stopDetails['lenses'][$lensKey]['contents'] = array();
            
            foreach ($stop->getLensContents($lensKey) as $lensContent) {
                $stopDetails['lenses'][$lensKey]['contents'][] = $this->formatLensContent($lensContent);
            }
        }
        
        return $stopDetails;
    }
    
    protected function getAllStopsBriefDetails($tour) {
        $stopsDetails = array();
        
        foreach ($tour->getAllStops() as $stop) {
            $stopsDetails[] = $this->getBriefStopDetails($stop);
        }
        return $stopsDetails;
    }
    
    protected function getTourDetails($tour) {
        $tourDetails = array(
            'pages' => array(
                'welcome' => array(),
                'finish'  => array(),
                'help'    => array(),
            ),
            'legend'  => array(),
            'updated' => $tour->getLastUpdate(),
        );
        
        $pages = array('welcome', 'finish', 'help');
        foreach (array_keys($tourDetails['pages']) as $page) {
            $pageObjects = array();
        
            switch ($page) {
                case 'welcome':
                    $pageObjects = $tour->getWelcomePageContents();
                    break;
                    
                case 'finish':
                    $pageObjects = $tour->getFinishPageContents();
                    break;
                    
                case 'help':
                    $pageObjects = $tour->getHelpPageContents();
                    break;
                    
                case 'legend':
                    $pageObjects = $tour->getStopDetailLegendContents();
                    break;
            }
            
            foreach ($pageObjects as $pageObject) {
                $tourDetails['pages'][$page][] = $pageObject->getContent();
            }
        }
        
        foreach ($tour->getStopDetailLegendContents() as $pageObject) {
            $tourDetails['legend'][] = $pageObject->getContent();
        }
        
        return $tourDetails;
    }
    
    protected function initializeForCommand() {
        $useCache = $this->command != 'refresh';
    
        $tour = new Tour($this->getArg('id', null), false, array(), $useCache);
        
        switch ($this->command) {
            case 'tour':
                $response = array(
                    'details' => $this->getTourDetails($tour),
                    'stops'   => $this->getAllStopsBriefDetails($tour),
                );
                
                $this->setResponse($response);
                $this->setResponseVersion(1);
                break;
                
            case 'stop':
                $response = $this->getStopDetails($tour->getCurrentStop());
                
                $this->setResponse($response);
                $this->setResponseVersion(1);
                break;
                
            case 'refresh':
                $this->setResponse(true);
                $this->setResponseVersion(1);
                break;        
        }
    }
}
