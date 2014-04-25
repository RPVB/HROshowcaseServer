<?php

namespace Album\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend;

class FileController extends AbstractActionController {

    public function uploadAction() {
        
        $adapter = new Zend\File\Transfer\Adapter\Http();

        $adapter->setDestination('/home/rowan/lamp/uploads');

        if (!$adapter->receive()) {
            $messages = $adapter->getMessages();
            echo implode("\n", $messages);
        }
        return $this->redirect()->toRoute('album/add');
    }

}
