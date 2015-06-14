<?php

namespace Qualisoft\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; //@diegotorres50: necesario para las anotaciones de rutas 
use Symfony\Component\HttpFoundation\Request; //@diegotorres50: necesario para el formulario
use Symfony\Component\Validator\Constraints\NotBlank; //@diegotorres50: para las restricciones de campos vacios

class SecurityController extends Controller
{
	 /**
     * @Route("/login", name="qualisoft_security_login") 
     */
    public function loginAction(Request $request)
    {


	    $defaultData = array('message' => 'Type your message here');

	    $form = $this->createFormBuilder($defaultData)
            ->setAction($this->generateUrl('qualisoft_security_login'))
            ->setMethod('POST')
            //->setAttribute('class', 'form-horizontal')) Tarea: como agregar atriburos custom al form tag
	        ->add('userId', 'text', array(
                'constraints' => array(
                    new NotBlank(
                        array(
                            'message' => 'Por favor indique el usuario.')
                        ),
                 ),
                'label' => 'Usuario*', 
                'label_attr' => array('class' => 'control-label col-md-3'),
                'mapped' => false,
                'required' => true,
                'error_bubbling' => true,
                'attr' => array(
                    'class' => 'form-control',
                    'data-validate-words' => '1', //Valida campo en javascript, https://github.com/yairEO/validator
                    'placeholder' => 'Escribe tu usuario.',
                    ),                
                'empty_data'  => null,         
                )
            )
            ->add('userPass', 'password', array(
                'constraints' => array(
                    new NotBlank(
                        array(
                            'message' => 'Por favor indique la clave.')
                        ),
                 ),
                'label' => 'Clave:', 
                'label_attr' => array('class' => 'control-label col-md-3'),
                'mapped' => false,
                'required' => true,
                'error_bubbling' => true,
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Escribe tu clave.',
                    ),                
                'empty_data'  => null,                 
                )
            )
	        ->add('sendData', 'submit', array(
                'attr' => array(
                    'class' => 'btn btn-success',
                    ),
                'label' => 'Ingresar',
                )
             )      
	        ->getForm();
	 
	    $form->handleRequest($request);
	 
	    if ($form->isValid()) {
            // data es un array con claves 'name', 'email', y 'message'
	        $data = $form->getData();
            var_dump($data); exit;
            $nextAction = $form->get('saveAndAdd')->isClicked()
                    ? 'task_new'
                    : 'task_success';

            return $this->redirectToRoute($nextAction);

	    }
	 
        /**
         * [$view_info description datos generales de la vista a renderizar]
         * @var array
         */
        $view_info = array(
                    'title' => 'Login',
                    'module_title' => 'Zona de Seguridad',
                    'module_subtitle' => 'Login de Acceso',
                    'module_lead' => 'Para acceder a todas las funciones debe ingresar sus credenciales.', 
                    );

	    // ... renderiza el formulario ...
        return $this->render('QualisoftAppBundle:Security:login.html.twig', 
            array(
                'form' => $form->createView(),
                'view_info' => $view_info
                )
            );


/* Para validar manualmente el formulario
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
 
$builder
   ->add('firstName', 'text', array(
       'constraints' => new Length(array('min' => 3)),
   ))
   ->add('lastName', 'text', array(
       'constraints' => array(
           new NotBlank(),
           new Length(array('min' => 3)),
       ),
   ))
;

*/


/*
CansinO:

        if($request->getMethod()=="POST")
        {
            $email=$request->get("correo");
            $password=$request->get("pass");
            //echo "correo=".$correo."<br>pass=".$pass;exit;
            $user=$this->getDoctrine()->getRepository('bdBundle:Usuarios')->findOneBy(array("correo"=>$email,"pass"=>$password));
            if($user)
            {
               $session=$request->getSession();
               $session->set("id",$user->getId());
               $session->set("nombre",$user->getNombre());
               //echo $session->get("nombre");exit;
               return $this->redirect($this->generateUrl('bd_homepage'));
            }else
            {
                 $this->get('session')->getFlashBag()->add(
                                'mensaje',
                                'Los datos ingresados no son válidos'
                            );
                    return $this->redirect($this->generateUrl('bd_homepagelogin'));
            }
        }
        
        return $this->render('bdBundle:Trabajo:login.html.twig');
*/




        
    }
      
	 /**
     * @Route("/logout", name="qualisoft_security_logout") 
     */      
    public function logoutAction(Request $request)
    {
        $session=$request->getSession();
        $session->clear();
        $this->get('session')->getFlashBag()->add(
                                'mensaje',
                                'Se ha cerrado sessión exitosamente, gracias por visitarnos'
                            );
                    return $this->redirect($this->generateUrl('bd_homepagelogin'));
    }

}


/*

Para la plantilla

{# src/Acme/TaskBundle/Resources/views/Form/fields.html.twig #}
{% block form_row %}
{% spaceless %}
    <div class="form_row">
        {{ form_label(form) }}
        {{ form_errors(form) }}
        {{ form_widget(form) }}
    </div>
{% endspaceless %}
{% endblock form_row %}


{# src/Acme/TaskBundle/Resources/views/Default/new.html.twig #}
{{ form_start(form) }}
    {{ form_errors(form) }}
 
    {{ form_row(form.task) }}
    {{ form_row(form.dueDate) }}
 
    {{ form_rest(form) }}
 
    <input type="submit" />
{{ form_end(form) }}



TRUCO Para acceder a los datos del formulario, utiliza la notación form.vars.value:

TWIG
PHP
{{ form.vars.value.task }}


12.5.1. Mostrando cada campo a mano
El helper form_row es muy útil porque puedes mostrar fácilmente cada campo del formulario (y también puedes personalizar su aspecto). Pero a veces necesitas un control todavía más preciso de cómo se muestra cada una de las partes que forman el campo de formulario. Para ello tendrás que utilizar otros helpers, tal y como muestra el siguiente código (que produce un resultado similar a utilizar el helper form_row):

TWIG
PHP
{{ form_start(form) }}
    {{ form_errors(form) }}
 
    <div>
        {{ form_label(form.task) }}
        {{ form_errors(form.task) }}
        {{ form_widget(form.task) }}
    </div>
 
    <div>
        {{ form_label(form.dueDate) }}
        {{ form_errors(form.dueDate) }}
        {{ form_widget(form.dueDate) }}
    </div>
 
    <div>
        {{ form_widget(form.save) }}
    </div>
 
{{ form_end(form) }}
Si el título generado automáticamente para un campo no es del todo correcto, puedes especificarlo explícitamente:

TWIG
PHP
{{ form_label(form.task, 'Task Description') }}
Algunos tipos de campo definen opciones de configuración relacionadas con la forma en la que se muestran. Estas opciones están documentadas con cada tipo, pero una opción común es attr, que te permite modificar los atributos del elemento del formulario. El siguiente ejemplo añade la clase task_field de CSS al elemento HTML utilizado para representar el campo task:

TWIG
PHP
{{ form_widget(form.task, { 'attr': {'class': 'task_field'} }) }}
Si necesitas renderizar los campos de formulario a mano, también puedes acceder a los valores individuales de cada campo (como el id, el name y el label). Por ejemplo, para obtener el id:

TWIG
PHP
{{ form.task.vars.id }}
Para obtener el valor del atributo name del campo de formulario, utiliza en su lugar la propiedad full_name:

TWIG
PHP
{{ form.task.vars.full_name }}


        $form = $this->createFormBuilder($task)
            ->add('task', 'text')
            ->add('dueDate', 'date')
            ->add('save', 'submit')
            ->getForm();
 
        return $this->render('AcmeTaskBundle:Default:new.html.twig', array(
            'form' => $form->createView(),
        ));

*/