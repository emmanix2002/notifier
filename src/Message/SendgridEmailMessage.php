<?php

namespace Emmanix2002\Notifier\Message;

class SendgridEmailMessage extends EmailMessage
{
    /**
     * @var array
     */
    private $sections;
    
    private $category;
    
    /**
     * @var bool
     */
    private $usesTemplate;
    
    public function __construct(
            string $from = null,
            string $subject = null,
            string $body = null,
            string $replyTo = null,
            array $sections = []
    ) {
        parent::__construct($from, $subject, $body, $replyTo);
        $this->sections = $sections;
        $this->usesTemplate = false;
        $this->category = null;
    }
    
    /**
     * Sets the template id on the message as the body
     *
     * @param string $templateId
     *
     * @return $this
     */
    public function setTemplateId(string $templateId)
    {
        $this->body = $templateId;
        $this->usesTemplate = true;
        return $this;
    }
    
    /**
     * Sets the message category/tag
     *
     * @param string $category
     *
     * @return $this
     */
    public function setCategory(string $category)
    {
        $this->category = $category;
        return $this;
    }
    
    /**
     * Sets the section array
     *
     * @param array $sections
     *
     * @return $this
     */
    public function setSections(array $sections)
    {
        $this->sections = $sections;
        return $this;
    }
    
    /**
     * Adds a new section entry
     *
     * @param string $key
     * @param        $value
     *
     * @return $this
     */
    public function addSectionData(string $key, $value)
    {
        $this->sections[$key] = $value;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getCategory(): string
    {
        return (string) $this->category;
    }
    
    /**
     * @return array
     */
    public function getSections(): array
    {
        return $this->sections;
    }
    
    /**
     * @return bool
     */
    public function usesTemplate(): bool
    {
        return (bool) $this->usesTemplate;
    }
}