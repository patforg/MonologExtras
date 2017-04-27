<?php
/**
 * dispatch log events to registered callback functions
 *
 * @since  2017-04-26
 * @author Patrick Forget <patforg@geekpad.ca>
 */

namespace MonologExtras\Handler; 

use \Monolog\Handler\AbstractProcessingHandler;

/**
 * dispatch log events to registered callback functions
 *
 * @since  2017-04-26

 * @author Patrick Forget <patforg@geekpad.ca>
 */
class CallbackHandler extends AbstractProcessingHandler 
{

    /**
     * All Levels
     */
    const ALL_LEVELS = -1;

    /**
     * @var array
     */
    private $handlers = array();

    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record)
    {
      // handle events that we have registered callbacks for
      if (parent::isHandling($record)) {
        return (isset($this->handlers[$record['level']]) 
          && count($this->handlers[$record['level']]) > 0)
          ||
          (isset($this->handlers[self::ALL_LEVELS]) 
          && count($this->handlers[self::ALL_LEVELS]) > 0);
      } else {
        return false;
      } //if
    }

    /**
     * add listenner
     *
     * @params $levels mixed Error level to handle, either 
     *     a single level to listen to e.g. \Monolog\Logger::ERROR
     *     an array of levels to listen to
     *     or null will listen to all levels
     */
    public function addListenner($levels, $handlerFunction) {

        if (!is_array($levels)) {
            $levels = array($levels);
        } //if

        foreach ($levels as $level) {
            if ( !isset($this->handlers[$level]) ) {
                $this->handlers[$level] = array();
            } //if

            $this->handlers[$level][] = $handlerFunction;

        } //foreach
    } // addListenner()

    /**
     * {@inheritDoc}
     */
    protected function write(array $record) {
        $bubble = true;

        $handlers = array();
        
        if (isset($this->handlers[$record['level']])) {
            $handlers = array_merge($handlers, $this->handlers[$record['level']]);
        } //if

        if (isset($this->handlers[self::ALL_LEVELS])) {
            $handlers = array_merge($handlers, $this->handlers[self::ALL_LEVELS]);
        } //if

        foreach ($handlers as $handler) {
            $tmpBubble = true;
            $tmpBubble = $handler($record);

            if ($tmpBubble === false) {
                $bubble = false;
            } //if
        } //foreach 

        if ($bubble === false) {
            $this->bubble = false;
        } //if
    } // write()

} //  CallbackHandler class
