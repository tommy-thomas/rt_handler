<?php

/**
 * Class RequestTrackerContent
 */
class RequestTrackerContent
{

    private $queue_description;
    private $queue_name;
    private $cnetid;
    private $email;
    private $attachments = array();
    private $content_array = array();
    private $content = array();
    private $ticket_text = "";

    /**
     * @param string $queue_description
     * @param string $queue_name
     * @param string $cnetid
     * @param string $email
     * @param array $content_array
     * @param array $attachments
     */
    public function __construct($queue_description = "", $queue_name = "", $cnetid = "", $email = "", $content_array = array(), $attachments = array())
    {
        $this->queue_name = $queue_name;
        $this->queue_description = $queue_description;
        $this->cnetid = $cnetid;
        $this->email = $email;
        $this->content_array = $content_array;
        $this->$attachments = $attachments;
        $this->setTicketText();
        $this->setTicketContent();
    }

    /**
     * Set Ticket Text for RT ticket.
     */
    public function setTicketText()
    {
        $proposal_text = "";
        $this->ticket_text .= "\n$this->queue_description\n";
        if( !empty($this->cnetid) ){
            $this->ticket_text .= "User: $this->cnetid \n";
        }
        if (!empty($this->content_array)) {
            foreach ($this->content_array as $key => $value) {
                if ($key === 'allocation_proposal' && !empty($value)) {
                    $proposal_text .= $value;
                    if (!empty($this->attachments)) {
                        $proposal_text .= "\n[attachment";
                        foreach ($this->attachments as $a) {
                            $proposal_text .= " " . $a['filename'];
                        }
                        $proposal_text .= "]";
                    }
                    $proposal_text = implode("\n ", explode("\n", $proposal_text));
                } else {
                    if ($this->notFile($key)) {
                        $value = $this->isDateField($key) ? $this->getDateFieldAsString($key) : $value;
                        $this->ticket_text .= ucwords(str_replace("_", " ", $key)) . ": " . $value . "\n";
                    }
                }
            }
            if( !empty($proposal_text))
            {
                $this->ticket_text .= "Proposal Text: $proposal_text";
            }
        }
    }

    /**
     * @internal param array $content_array
     */
    public function setTicketContent()
    {
        $this->content['Queue'] = $this->queue_name;
        $this->content['Owner'] = 'RT-Drupal';
        $this->content['Requestor'] = $this->email;
        $this->content['Subject'] = $this->queue_description;
        foreach ($this->content_array as $key => $value) {
            if ($this->notFile($key)) {
                $value = $this->isDateField($key) ? $this->getDateFieldAsString($key) : $value;
                $new_key = 'CF-' . str_replace("_", "-", $key);
                $this->content[$new_key] = $value;
            }
        }
        $this->content['Text'] = $this->ticket_text;
    }

    /**
     * @param $field_name
     * @return bool
     */
    public function isDateField($field_name)
    {
        $isArray = is_array($this->content_array[$field_name]);
        $test = $this->content_array[$field_name];
        $isDataArray = $isArray && (isset($test['month']) && isset($test['day']) && isset($test['year']));
        return $isDataArray;
    }

    /**
     * @param $field_name
     * @return string
     */
    public function getDateFieldAsString($field_name)
    {
        return implode("/", $this->content_array[$field_name]);
    }

    public function notFile($key)
    {
        $filter = strpos($key , 'file');
        return !$filter ? true : false;
    }

    /**
     * @return string
     */
    public function getTicketText()
    {
        return $this->ticket_text;
    }

    /**
     * @return array
     */
    public function getTicketContent()
    {
        return $this->content;
    }
}