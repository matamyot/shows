<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/shows", name="shows")
     * @Template()
     */
    public function showsAction(Request $pages)
    {
        $em = $this->get('doctrine')->getManager();
        $repo = $em->getRepository('AppBundle:TVShow');
        // *********************************************************
        // Question 4 : Gestion du bundle knp paginator selon la doc
        // _________________________________________________________
        $shows = $repo->findAll();
        $paginator = $this->get("knp_paginator");
        $pagination = $paginator->paginate(
            $shows,
            $pages->query->getInt("page",1),6);

        return [
            'shows' => $pagination
        ];
    }

    /**
     * @Route("/show/{id}", name="show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->get('doctrine')->getManager();
        $repo = $em->getRepository('AppBundle:TVShow');

        return [
            'show' => $repo->find($id)
        ];        
    }

    // ************
    // Question 4 :
    // ____________
    /**
     * @Route("/calendar", name="calendar")
     * @Template()
     */
    public function calendarAction()
    {
        $em = $this->getDoctrine()->getManager();
        $rep = $em->getRepository('AppBundle:Episode');
        $eps = $rep->findByComingNext();

        return [
            'episodes' => $eps
        ];
    }

    /**
     * @Route("/login", name="login")
     * @Template()
     */
    public function loginAction()
    {
        return [];
    }

    // ************
    // Question 3 :
    // ____________
    /**
     * @Route("/search", name="search")
     * @Template()
     */
    public function searchAction(Request $search) //Request : Contient tout ce que le client envoie
    {
        $em = $this->get('doctrine')->getManager(); 
        $shows = $em->getRepository('AppBundle:TVShow')->findByResearch($search->get('query'));  // recupere contenu input name = query
        return [
            'shows' => $shows
        ];  
    }

}
