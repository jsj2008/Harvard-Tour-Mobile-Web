<?php

/*
 * Copyright © 2010 - 2012 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class MoodlePageCourseContent extends PageCourseContent {   
    public function getContent() {
        if($retriever = $this->getContentRetriever()){
            $content = $retriever->getFileForUrl($this->getURL(), '');
            return $content;
        }
    }
}
