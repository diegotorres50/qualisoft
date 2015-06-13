<?php

namespace QualisoftAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('QualisoftAppBundle:Default:index.html.twig', array('name' => $name));
    }
}
