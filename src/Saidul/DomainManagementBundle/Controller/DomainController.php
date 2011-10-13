<?php

namespace Saidul\DomainManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Saidul\DomainManagementBundle\Helper\DomainHelper;
use Saidul\DomainManagementBundle\Form\DomainType;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DomainController extends Controller {
    //put your code here
    
    /**
     * @Route("/", name="_domain_index")
     * @return Response 
     */
    public function indexAction(){
        
        return new Response("Hello World");
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
                DomainHelper::addDomain($data['host'], $data['ip']);
                return new RedirectResponse($this->generateUrl('_domain_list'));
            }
        }

        return $this->render("SaidulDomainManagementBundle:Domain:form.html.twig",array(
                'form' => $form->createView(),
                'submit_url' => $this->generateUrl('_domain_new')
        ));
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
}

?>