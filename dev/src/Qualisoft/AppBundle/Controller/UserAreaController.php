<?php

namespace Qualisoft\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; //@diegotorres50: necesario para las anotaciones de rutas 

class UserAreaController extends Controller
{
	 /**
     * @Route("/login", name="qualisoft_app_userarea_login") 
     */
    public function loginAction()
    {
        return $this->render('QualisoftAppBundle:UserArea:login.html.twig', array('var' => 'Login aquí'));
    }
      
}
