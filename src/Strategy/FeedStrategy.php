<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Strategy;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\Feed\Writer\Feed;
use Laminas\Http\Request as HttpRequest;
use Laminas\View\Model;
use Laminas\View\Renderer\FeedRenderer;
use Laminas\View\ViewEvent;

/**
 * @category   Laminas
 * @package    Laminas_View
 * @subpackage Strategy
 */
class FeedStrategy implements ListenerAggregateInterface
{
    /**
     * @var \Laminas\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * @var FeedRenderer
     */
    protected $renderer;

    /**
     * Constructor
     *
     * @param  FeedRenderer $renderer
     */
    public function __construct(FeedRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Attach the aggregate to the specified event manager
     *
     * @param  EventManagerInterface $events
     * @param  int $priority
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RENDERER, array($this, 'selectRenderer'), $priority);
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RESPONSE, array($this, 'injectResponse'), $priority);
    }

    /**
     * Detach aggregate listeners from the specified event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * Detect if we should use the FeedRenderer based on model type and/or
     * Accept header
     *
     * @param  ViewEvent $e
     * @return null|FeedRenderer
     */
    public function selectRenderer(ViewEvent $e)
    {
        $model = $e->getModel();

        if (!$model instanceof Model\FeedModel) {
            // no FeedModel present; do nothing
            return;
        }

        // FeedModel found
        return $this->renderer;
    }

    /**
     * Inject the response with the feed payload and appropriate Content-Type header
     *
     * @param  ViewEvent $e
     * @return void
     */
    public function injectResponse(ViewEvent $e)
    {
        $renderer = $e->getRenderer();
        if ($renderer !== $this->renderer) {
            // Discovered renderer is not ours; do nothing
            return;
        }

        $result   = $e->getResult();
        if (!is_string($result) && !$result instanceof Feed) {
            // We don't have a string, and thus, no feed
            return;
        }

        // If the result is a feed, export it
        if ($result instanceof Feed) {
            $result = $result->export($renderer->getFeedType());
        }

        // Get the content-type header based on feed type
        $feedType = $renderer->getFeedType();
        $feedType = ('rss' == $feedType)
                  ? 'application/rss+xml'
                  : 'application/atom+xml';

        $model   = $e->getModel();
        $charset = '';

        if ($model instanceof Model\FeedModel) {

            $feed = $model->getFeed();

            $charset = '; charset=' . $feed->getEncoding() . ';';
        }

        // Populate response
        $response = $e->getResponse();
        $response->setContent($result);
        $headers = $response->getHeaders();
        $headers->addHeaderLine('content-type', $feedType . $charset);
    }
}
