<?php

namespace RGies\GuiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Yaml\Yaml;
/**
 * FormGenerator controller.
 *
 */
class FormGeneratorController extends Controller
{
    /**
     * @Route("/form-generator", name="guiFormGenerator")
     * @Template()
     */
    public function indexAction()
    {
        $elements = array();
        $config = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/config/form_elements.yml'));

        foreach($config['elements'] as $name => $element)
        {
            $elements[$element['category']][$name] = $element;
        }

        // render elements panel
        $response = $this->render( 'GuiBundle:FormElements:elements-panel.html.twig', array('elements' => $elements) );
        $html = $response->getContent();

        return array('elements_panel' => $html);
    }

    /**
     * Renderer for form elements.
     *
     * @Route("/form-render-element", name="guiRenderFormElement")
     * @Method("POST")
     * @Template()
     */
    public function renderFormElementAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
        {
            return new Response('No valid request', Response::HTTP_FORBIDDEN);
        }

        $name = strtolower($request->request->get('name'));
        $config = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/config/form_elements.yml'));

        if (!isset($config['elements'][$name]))
        {
            return new Response('Configuration for [' . $name . '] missing.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $vars = $config['elements'][$name]['vars'];

        if ($request->request->has('num'))
        {
            $num = $request->request->get('num');
            if (isset($vars['id']))
            {
                $vars['id'] .= '-' . $num;
            }
        }

        // update element
        if ($request->request->has('vars'))
        {
            $vars = array_merge($vars, $request->request->get('vars', array()));
        }

        $response = $this->render( 'GuiBundle:FormElements/items:' . $name . '.html.twig', array('vars' => $vars) );
        $html = $response->getContent();

        return new Response(
            json_encode(array('html' => $html, 'vars' => $vars)),
            Response::HTTP_OK
        );
    }

    /**
     * Renderer for attribute form.
     *
     * @Route("/form-render-attributes", name="guiRenderAttributesForm")
     * @Method("POST")
     * @Template()
     */
    public function renderAttributesFormAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
        {
            return new Response('No valid request', Response::HTTP_FORBIDDEN);
        }

        $vars = $request->request->get('vars', array());
        $name = $request->request->get('name');

        $config = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/config/form_elements.yml'));

        $element = $config['elements'][$name];

        $vars = array_merge($element['vars'], $vars);

        $element['vars'] = $vars;

        $response = $this->render( 'GuiBundle:FormElements:attributes-form.html.twig', array('element' => $element, 'name' => $name) );
        $html = $response->getContent();

        return new Response($html, Response::HTTP_OK);
    }

}
