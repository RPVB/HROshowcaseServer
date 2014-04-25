<?php

namespace Album\Controller;

use Zend;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Album\Model\Album;
use Album\Form\AlbumForm;

class AlbumController extends AbstractActionController {

    protected $albumTable;

    public function indexAction() {
        return new ViewModel(array(
            'albums' => $this->getAlbumTable()->fetchAll(),
        ));
    }

    public function addAction() {
        $form = new AlbumForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $album = new Album();
            $form->setInputFilter($album->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $dataArray = $form->getData();
                $dataArray['file'] = $_FILES['file']['name'];
                $album->exchangeArray($dataArray);
                $this->getAlbumTable()->saveAlbum($album);

                $adapter = new Zend\File\Transfer\Adapter\Http();

                $adapter->setDestination('/var/www/html/uploads');

                if (!$adapter->receive()) {
                    $messages = $adapter->getMessages();
                    echo implode("\n", $messages);
                }
                // Redirect to list of albums
                return $this->redirect()->toRoute('album');
            }
        }
        return array('form' => $form);
    }

    public function editAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('album', array(
                        'action' => 'add'
            ));
        }
        $album = $this->getAlbumTable()->getAlbum($id);

        $form = new AlbumForm();
        $form->bind($album);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($album->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getAlbumTable()->saveAlbum($form->getData());

                // Redirect to list of albums
                return $this->redirect()->toRoute('album');
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
        );
    }

    public function deleteAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('album');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $this->getAlbumTable()->deleteAlbum($id);
            }

            // Redirect to list of albums
            return $this->redirect()->toRoute('album');
        }

        return array(
            'id' => $id,
            'album' => $this->getAlbumTable()->getAlbum($id)
        );
    }

    public function viewAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        $file = $this->getAlbumTable()->getAlbum($id)->file;
        
        if($file != ""){

        $str = str_replace(".zip", "", $file);

        $zip = new ZipArchive;
        $zip->open("/var/www/html/uploads/".$file."");
        $zip->extractTo("/var/www/html/uploads");
        $zip->close();
        return $this->redirect()->toRoute('album');
        $output = shell_exec('unzip /var/www/html/uploads/'.$file.'');
        }
        else {
        return $this->redirect()->toRoute('album');
        }
    }

    public function getAlbumTable() {
        if (!$this->albumTable) {
            $sm = $this->getServiceLocator();
            $this->albumTable = $sm->get('Album\Model\AlbumTable');
        }
        return $this->albumTable;
    }

}
