<?php

namespace Qualisoft\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; //@diegotorres50: necesario para las anotaciones de rutas 

class SecurityController extends Controller
{
	 /**
     * @Route("/login", name="qualisoft_security_login") 
     */
    public function loginAction()
    {
        return $this->render('QualisoftAppBundle:Security:login.html.twig', array('var' => 'Login aquí'));
    }
      
}
