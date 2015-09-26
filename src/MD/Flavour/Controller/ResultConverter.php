<?php

namespace MD\Flavour\Controller;

use Negotiation\FormatNegotiator;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\Templating\EngineInterface;

use MD\Flavour\Controller\Result;

class ResultConverter
{

    /**
     * Templating engine.
     *
     * @var EngineInterface
     */
    protected $templating;

    /**
     * Constructor.
     *
     * @param EngineInterface $templating Templating engine.
     */
    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * Event listener called when a controller does not return a Response object.
     *
     * @param  GetResponseForControllerResultEvent $event The event.
     */
    public function onControllerResult(GetResponseForControllerResultEvent $event)
    {
        $controllerResult = $event->getControllerResult();

        if (is_array($controllerResult) || is_string($controllerResult) || is_numeric($controllerResult)) {
            $controllerResult = new Result($controllerResult);
        }

        // only take care of Tornado Controller Results
        if (!$controllerResult instanceof Result) {
            return;
        }

        $request = $event->getRequest();

        $response = $this->convertControllerResult($request, $controllerResult);

        $event->setResponse($response);
    }

    /**
     * Converts the passed data into a Response based on Request parameters.
     *
     * If HTTP_ACCEPT header expects `application/json` then it will wrap the `$result` in `JsonResponse`.
     *
     * Otherwise it will try to render a Twig template with the `$result` data passed to it. The template to render
     * is determined either by Request's `_template` attribute and if none found, then built based on controller name.
     *
     * @param  Request $request Request for which to render a response.
     * @param  Result  $result  Controller result.
     *
     * @return Response
     *
     * @throws \RuntimeException When could not determine template name.
     */
    public function convertControllerResult(Request $request, Result $result)
    {
        $formatNegotiator = new FormatNegotiator();
        $bestFormat = $formatNegotiator->getBest(
            $request->server->get('HTTP_ACCEPT'),
            ['application/json', 'text/html']
        );

        $responseData = $result->getData();

        if ($bestFormat && $bestFormat->getValue() === 'application/json') {
            return new JsonResponse($responseData, $result->getHttpCode());
        }

        // maybe there is a template set explicitly?
        $template = $request->attributes->get('_template');

        // if no template set explicitly then build one based on controller name
        if (!$template) {
            $template = $request->attributes->get('_controller');
            $template = preg_replace('/^controller\./i', '', $template);
            $template = preg_replace('/\.?controller:/i', '.', $template);
            $template = str_replace(['.', ':'], '/', $template) . '.html.twig';
        }

        // if still didn't build it then throw exception
        if (!$template || $template === '.html.twig') {
            throw new \RuntimeException(
                'Could not determine what template to render. '
                . 'Have you set "_template" or "_controller" attribute in your route definition?'
            );
        }

        $content = $this->templating->render($template, $responseData);
        return new Response($content, $result->getHttpCode());
    }
}
