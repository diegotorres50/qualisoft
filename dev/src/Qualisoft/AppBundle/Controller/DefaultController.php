<?php

namespace Qualisoft\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; //@diegotorres50: necesario para las anotaciones de rutas 
use Symfony\Component\HttpFoundation\Response; //@diegotorres50: para el response hello world

class DefaultController extends Controller
{
	 /**
     * @Route("/", name="qualisoft_app_homepage") 
     */
    public function indexAction()
    {
        return $this->render('QualisoftAppBundle:Default:index.html.twig', array('var' => 'Any Value Here'));
    }

	 /**
     * @Route("testing1/{var}", name="qualisoft_app_testing1")
     */
    public function testing1Action($var)
    {
        return new Response('Quiubo pues ' . $var);
    } 

	 /**
     * @Route("testing2/{var}", name="qualisoft_app_testing2")
     */
    public function testing2Action($var)
    {
        return $this->render('QualisoftAppBundle:Default:index.html.twig', array('var' => $var));
    }            
}
