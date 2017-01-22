<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Forms\ShowType;
use AppBundle\Forms\EpisodeType;
use AppBundle\Entity\TVShow;
use AppBundle\Entity\Season;
use AppBundle\Entity\Episode;
use aharen\OMDbAPI;

/**
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * @Route("/addShow", name="admin_add_show")
     * @Template()
     */
    public function addShowAction(Request $request)
    {
    	$show = new TVShow;
    	$form = $this->createForm(ShowType::class, $show);
    	$success = false;

		$form->handleRequest($request);
    	if ($form->isSubmitted() && $form->isValid()) {
    		$file = $show->getImage();
    		if ($file) {
                // Handling file upload
    			$filename = md5(uniqid()).'.'.$file->guessExtension();
    			$webRoot = $this->get('kernel')->getRootDir().'/../web';

    			$file->move($webRoot . '/uploads', $filename);
    			$show->setImage($filename);
    		}

    		$em = $this->get('doctrine')->getManager();
    		$em->persist($show);
    		$em->flush();
    		$success = true;
    	}

        return [
        	'form' => $form->createView(),
        	'success' => $success
        ];
    }

    /**
     * @Route("/addSeason/{id}", name="admin_add_season")
     */
    public function addSeasonAction($id)
    {
        $em = $this->get('doctrine')->getManager();
        $repo = $em->getRepository('AppBundle:TVShow');

        if ($show = $repo->find($id)) {
            $season = new Season;
            $season
                ->setShow($show)
                ->setNumber(count($show->getSeasons())+1)
                ;
            $em->persist($season);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('show', ['id' => $id]));
    }
    // ******************************************************************************************
    // Question 2 : Creation de l'action removeSeason + ajout du path (basé sur le deleteEpisode)
    // __________________________________________________________________________________________
    /**
     * @Route("/deleteSeason/{id}", name="admin_delete_season")
     */
    public function removeSeasonAction($id)
    {
        $em = $this->get('doctrine')->getManager(); //Recupere l'instance du manager de Doctrine -> recup les repos
        $repo = $em->getRepository('AppBundle:Season'); //Recupere le repo (pont entre les entity et la base)
        if ($season = $repo->find($id)) { // Demande l'objet saison qui correspond à l'id
            $id = $season->getShow()->getId();
            $em->remove($season); // appel la commande remove
            $em->flush(); // Application des changements
            return $this->redirect($this->generateUrl('show', ['id' => $id]));
        } else {
            return $this->redirect($this->generateUrl('homepage'));
        }
    }

    /**
     * @Route("/deleteEpisode/{id}", name="admin_delete_episode")
     */
    public function deleteEpisodeAction($id)
    {
        $em = $this->get('doctrine')->getManager();
        $repo = $em->getRepository('AppBundle:Episode');
        if ($episode = $repo->find($id)) {
            $id = $episode->getSeason()->getShow()->getId();
            $em->remove($episode);
            $em->flush();
            return $this->redirect($this->generateUrl('show', ['id' => $id]));
        } else {
            return $this->redirect($this->generateUrl('homepage'));
        }
    }

    /**
     * @Route("/addEpisode/{id}", name="admin_add_episode")
     * @Template()
     */
    public function addEpisodeAction($id, Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $repo = $em->getRepository('AppBundle:Season');

        if ($season = $repo->find($id)) {
            $episode = new Episode;
            $episode
                ->setSeason($season)
                ->setNumber(count($season->getEpisodes())+1)
                ;

            $form = $this->createForm(EpisodeType::class, $episode);

            $form->handleRequest($request);
        	if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($episode);
                $em->flush();
                return $this->redirect($this->generateUrl('show',[
                    'id' => $episode->getSeason()->getShow()->getId()
                ]));
            }
        } else {
            return $this->redirect($this->generateUrl('homepage'));
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/omdb", name="admin_omdb")
     * @Template()
     */
    public function omdbAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('keyword')
            ->getForm()
            ;

        $result = [];
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $omdb = new OMDbAPI();
            $result = $omdb->search($data['keyword']);
            $result = $result->data->Search;
        }

        return [
            'form' => $form->createView(),
            'result' => $result
        ];
    }

}
