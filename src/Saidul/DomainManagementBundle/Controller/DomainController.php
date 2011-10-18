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
        $list = DomainHelper::findAllDomains();
        //echo "<pre>"; print_r($list); die();
        return $this->render("SaidulDomainManagementBundle:Domain:list.html.twig",array(
            'dlist'=>$list,
            'sMsg'=> $this->get('session')->getFlash('sMsg'),
            'eMsg'=> $this->get('session')->getFlash('eMsg'),
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
                if(DomainHelper::addDomain($data['host'], $data['ip']) == true )
                    $this->get('session')->setFlash('sMsg','New record has been added.');
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

        if(DomainHelper::removeRecordByIdx($idx)){
            $session->setFlash('sMsg','The Item has been deleted');
        }else{
            $session->setFlash('eMsg','Can not process your request');
        }
        return $this->redirect($this->generateUrl('_domain_list'));
    }

    /**
     * @Route("/delete", name="_domain_delete")
     * @return Response 
     */
    public function deleteAction(){
        //$list = DomainHelper::findAndRemoveDomains();
        //echo "<pre>"; print_r($list); die();
        
        $form = $this->get('form.factory')->create(new DomainType());
        $request = $this->get('request');
        
        if ('POST' == $request->getMethod()) {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                DomainHelper::findAndRemoveDomains($data['host'], $data['ip']);
                return new RedirectResponse($this->generateUrl('_domain_list'));
            }
        }
        
        return $this->render("SaidulDomainManagementBundle:Domain:form.html.twig",array(
                'form' => $form->createView(),
                'submit_url' => $this->generateUrl('_domain_delete')
        ));
        
    }


    /**
     * @Route("/edit/{idx}", name="_domain_edit_id")
     * @param $idx
     * @return Response
     */
    public function editAction($idx){
        /* @var $form \Symfony\Component\Form\Form */
        /** @var $session \Symfony\Component\HttpFoundation\Session */
        
         $form = $this->get('form.factory')->create(new DomainType());
        $request = $this->get('request');
        
        $session = $this->get('session');

        $dlist = DomainHelper::findAllDomains();
        if(isset($dlist[$idx])) $form->setData($dlist[$idx]);
        else throw new NotFoundHttpException("Domain Not found");
        
        if ('POST' == $request->getMethod()) {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                if(DomainHelper::updateDomainRecordByIndex($idx, $data['host'], $data['ip'])){
                    $session->setFlash('sMsg',"Data Updated");
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