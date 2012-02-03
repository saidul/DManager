<?php

namespace Saidul\DomainManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Saidul\DomainManagementBundle\Helper\DomainHelper;
use Saidul\DomainManagementBundle\Form\DomainType;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DomainController extends Controller {
    
    /**
     * @Route("/", name="_domain_index")
     * @return Response 
     */
    public function indexAction(){
        
        return $this->redirect($this->generateUrl('_domain_list'));
    }
    
        
    /**
     * @Route("/list", name="_domain_list")
     * @return Response 
     */
    public function listAction(){
        $form = $this->get('form.factory')->create(new DomainType());
        $list = DomainHelper::findAllDomains();
        $dirPath = $this->container->getParameter('domain_root_dir');
        $rootDirObj = dir($dirPath);
        $exisingDomainDirs = array();
        while (false !== ($entry = $rootDirObj->read())) {
            if($entry == '.' || $entry == '..') continue;
           $exisingDomainDirs[$entry] = 1;
        }
        $rootDirObj->close();

        foreach($list as &$record){
            if($record['local'] == true){
                $record['dir_exist'] = file_exists("{$dirPath}/{$record['host']}");
                if(isset($exisingDomainDirs[$record['host']])) unset($exisingDomainDirs[$record['host']]);
            }
        }
        //echo "<pre>"; print_r($list); die();
        return $this->render("SaidulDomainManagementBundle:Domain:list.html.twig",array(
            'dlist'=>$list,
            'danglingDirs' => $exisingDomainDirs,
            'sMsg'=> $this->get('session')->getFlash('sMsg'),
            'eMsg'=> $this->get('session')->getFlash('eMsg'),
            'form' => $form->createView(),
        ));
    }
 
    /**
     * @Route("/new", name="_domain_new")
     * @return Response 
     */
    public function newAction(){
        /* @var $form \Symfony\Component\Form\Form */
        $form = $this->get('form.factory')->create(new DomainType());
        $request = $this->get('request');
        

        
        if ('POST' == $request->getMethod()) {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                if(DomainHelper::addDomain($data['host'], $data['ip']) == true ){
                    $this->get('session')->setFlash('sMsg','New record has been added.');

                    $dirPath = $this->container->getParameter('domain_root_dir');
                    if(preg_match('/[\w\-\.]+.localhost.com/i',$data['host']) && file_exists($dirPath) && !file_exists($dirPath."/{$data['host']}")){
                        mkdir($dirPath."/{$data['host']}/web",0777,true); // recursively create directory
                        $sampleFileData = $this->renderView("SaidulDomainManagementBundle:Domain:sampleIndex.html.twig",array(
                            'domain' => $data['host']
                        ));

                        file_put_contents($dirPath."/{$data['host']}/web/index.html",$sampleFileData);
                    }
                }
                else
                    $this->get('session')->setFlash('eMsg','Could not add new host');
                return $this->redirect($this->generateUrl('_domain_list'));
            }
        }

        return $this->render("SaidulDomainManagementBundle:Domain:form.html.twig",array(
                'form' => $form->createView(),
                'submit_url' => $this->generateUrl('_domain_new')
        ));
    }

    /**
     * @Route("/delete/{idx}", name="_domain_delete_id")
     * @param $idx
     * @return Response
     */
    public function deleteByIdAction($idx){
        /** @var $session \Symfony\Component\HttpFoundation\Session */
        $session = $this->get('session');

        if(DomainHelper::removeRecordByHostName($idx)){
            $session->setFlash('sMsg','The Item has been deleted');
        }else{
            $session->setFlash('eMsg','Can not process your request');
        }
        return $this->redirect($this->generateUrl('_domain_list'));
    }


    /**
     * @Route("/{idx}/edit", name="_domain_edit_id")
     * @param $idx
     * @return Response
     */
    public function editAction($idx){
        /* @var $form \Symfony\Component\Form\Form */
        /** @var $session \Symfony\Component\HttpFoundation\Session */
        
        $form = $this->get('form.factory')->create(new DomainType());
        $request = $this->get('request');
        
        $session = $this->get('session');

        $hostInfo = DomainHelper::getInfoByHostName($idx);
        if(null !== $hostInfo) $form->setData($hostInfo);
        else throw new NotFoundHttpException("Domain Not found");

        $dirPath = $this->container->getParameter('domain_root_dir');
        $oldDirName = "{$dirPath}/{$hostInfo['host']}";
        
        if ('POST' == $request->getMethod()) {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                if(DomainHelper::updateDomainRecordByHostName($idx, $data['host'], $data['ip'])){
                    $session->setFlash('sMsg',"Data Updated");
                    $newDirName = "{$dirPath}/{$data['host']}";
                    if(preg_match('/[\w\-\.]+.localhost.com/i',$data['host']) && file_exists($oldDirName))
                        rename($oldDirName,$newDirName);

                }
                 
                return new RedirectResponse($this->generateUrl('_domain_list'));
            }
        }

        return $this->render("SaidulDomainManagementBundle:Domain:form.html.twig",array(
                'form' => $form->createView(),
                'submit_url' => $this->generateUrl('_domain_edit_id',array('idx'=>$idx))
        ));
    }
}

?>