<?php
class Kega_Exception extends Exception
{
    const EMERG		= 0;	// Emergency: system is unusable
	const ALERT		= 1;	// Alert: action must be taken immediately
	const CRIT		= 2;	// Critical: critical conditions
	const ERR		= 3;	// Error: error conditions
	const WARN		= 4;	// Warning: warning conditions
	const NOTICE	= 5;	// Notice: normal but significant condition
	const INFO		= 6;	// Informational: informational messages
	const DEBUG		= 7;	// Debug: debug messages
	
	const DEFAULT_PRIORITY = Kega_Exception::ERR;

    private $_priority = null;

    // no action required
    public function __construct($message = null, $code = 0, $priority = Kega_Exception::DEFAULT_PRIORITY) {

        // set priority
        $this->_priority = $priority;

    	parent::__construct($message, $code);
    }

    public function getPriority()
    {
        return $this->_priority;
    }

    public function getPriorityName() {

        $name = 'UNKNOWN';

        $priorityNames = array(0 => 'EMERG',    // Emergency: system is unusable
                               1 => 'ALERT',    // Alert: action must be taken immediately
                               2 => 'CRIT',     // Critical: critical conditions
                               3 => 'ERR',      // Error: error conditions
                               4 => 'WARN',     // Warning: warning conditions
                               5 => 'NOTICE',   // Notice: normal but significant condition
                               6 => 'INFO',     // Informational: informational messages
                               7 => 'DEBUG');   // Debug: debug messages

        if(!empty($priorityNames[$this->_priority]))
            $name = $priorityNames[$this->_priority];

        return $name;
    }

}