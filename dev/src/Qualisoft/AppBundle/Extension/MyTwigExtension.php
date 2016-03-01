<?php 

namespace Qualisoft\AppBundle\Extension;

/* 
@diegotorres50 says: Inicia: Añadir funciones a Twig en Symfony 2 (fuente: http://ahoj.io/how-to-make-twig-extension-for-symfony2)

Ahora solo tenemos que extender Twig, esto lo haremos creando una simple extensión que por ejemplo añade una función que transforma una cadena de tiempo en un timestampable de Unix:
*/

class MyTwigExtension extends \Twig_Extension {

    /*
    * getFilters permite hacer los llamados del modo: {{ 'dump some variables'|var_dump }} o {{ 'Highlight this sentence'|highlight('i')|raw }}
    */
    public function getFilters() {
        return array(
            'var_dump'   => new \Twig_Filter_Function('var_dump'),
            'highlight'  => new \Twig_Filter_Method($this, 'highlight'),
        );
    }

    /**
     * {@inheritdoc}
     * getFunctions permite hacer los llamados del modo: {{ tomd5('diego') }} o por ejemplo {{ totime('diego') }} 
     */
    public function getFunctions()
    {
        return array(
            'totime' => new \Twig_Function_Method($this, 'toTime'),
            'tomd5' => new \Twig_Function_Method($this, 'toMd5')
        );
    }
    
    public function highlight($sentence, $expr) {
        return preg_replace('/(' . $expr . ')/',
                            '<span style="color:red">\1</span>', $sentence);
    }

    /**
     * Converts a string to time
     *
     * @param string $string
     * @return int
     */
    public function toTime ($string)
    {
        return strtotime($string);
    }
    /* Eso es todo, ahora para utilizar nuestra nueva función en las plantillas Twig solo tenemos que invocar la función totime(): */   

    /**
     * Converts a string to md5
     *
     * @param string $string
     * @return int
     */
    public function toMd5 ($string)
    {
        return md5($string);
    }
    /* Ahora desde twig se puede llamar el md5 asi: {{ tomd5('diego') }} */

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'my_twig_extension';
    }

    /*
    And that's all. Now we can test the output in Twig template.

    {{ 'dump some variables'|var_dump }}
    {{ 'Highlight this sentence'|highlight('i')|raw }}
    We have to turn off autoescaping because highlight function returns HTML tags, that's why there's the |raw filter at the end. Visual output is following ... (note: I have xdebug extension enabled so my var_dump might be different to yours):
    */

}

/*
It's too much work... (fuente: http://ahoj.io/how-to-make-twig-extension-for-symfony2)
There's an alternative way of enabling Twig extensions. As it's described in Service Container official documentation documentation you can do the same by just adding a few lines in your configuration file.

# app/config/config.yml
services:
    foo.twig.extension:
        class: Bundle\HelloBundle\Extension\HelloExtension
        tags:
            -  { name: twig.extension }
This is nice when you're making some single purpose bundle but if you wanted to reuse it among more applications you would have to enable it manually every time.
*/