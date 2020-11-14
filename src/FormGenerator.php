<?php

namespace FormGenerator;

use FormGenerator\Types\FormTypeInterface;

class FormGenerator
{
    /**
     * @var array[]
     */
    private array $fields;

    /**
     * @var string[]
     */
    private array $types = [
            'button',
            'checkbox',
            'color',
            'date',
            'datetime-local',
            'email',
            'file',
            'hidden',
            'image',
            'month',
            'number',
            'password',
            'radio',
            'range',
            'reset',
            'search',
            'submit',
            'tel',
            'text',
            'time',
            'url',
            'week'
        ];

    private FormConfig $config;

    public function __construct(?FormConfig $config = null)
    {
        $this->config = null !== $config ? $config : new FormConfig();
    }

    /**
     * Add a field to the form.
     *
     * @param string                        $name    Name of the field
     * @param string|FormTypeInterface|null $type    Type of the field (default is text)
     * @param string[]                      $options Addtional options
     *
     * @return FormGenerator
     */
    public function add(string $name, $type = null, array $options = []): self
    {
        $type = isset($type) ? $type : $this->getType($name);
        $field = [
            'name'  => $name,
            'id'    => "field-{$name}",
            'value' => '',
            'type'  => $type
        ];
        if (\is_object($type)) {
            $field['type'] = $type->getType();
            if ('select' === $field['type']) {
                $field['options'] = $type->getData();
            }
        }
        $field = array_merge($field, $options);
        $this->fields[] = $field;

        return $this;
    }

    /**
     * Generate the form.
     *
     * @throws Exception\FormConfigException
     */
    public function generate(): ?string
    {
        if ([] === $this->fields) {
            return null;
        }
        $html_structure = $this->config->get('FULL_HTML_STRUCTURE');
        $class = null !== $this->config->get('FORM_CLASS') ? " class=\"{$this->config->get('FORM_CLASS')}\"" : '';
        $form = '';

        if ($html_structure === true) {
            $submitValue = $this->config->get('FORM_SUBMIT_VALUE')
                ? $this->config->get('FORM_SUBMIT_VALUE') : '';
            $submit = $this->config->get('FORM_SUBMIT') ? "\n    <input type=\"submit\"{$submitValue}>" : "";
            $form = <<<HTML
<form method="{$this->config->get('FORM_METHOD')}" action=""{$class}>
    {$this->getGeneratedFields()}{$submit}
</form>
HTML;
        } elseif ($html_structure === false) {
            $form = $this->getGeneratedFields();
        }
        if (true === $this->config->get('EMPTY_GENERATED_FIELD')) {
            $this->fields = [];
        }
        return $form;
    }

    /**
     * Generate a select with correct parameters and options given in parameters.
     *
     * @param string[] $field Arrays contains parameters and options
     */
    private function select(array $field): string
    {
        // Phpcs:disable
        return <<<HTML
<select id="{$field['id']}" name="{$field['name']}[]" {$this->getRequired($field)}{$this->getClass($field)}>{$field['options']}\n</select>
HTML;
        //Phpcs:enable
    }

    /**
     * Generate a label with correct parameters.
     *
     * @param string[] $field Aray contains parameters
     */
    private function label(array $field): string
    {
        return <<<HTML
<label for="{$field['id']}">{$field['label']}</label>\n
HTML;
    }

    /**
     * Generate an input with parameters given in parameters.
     *
     * @param string[] $field Arrays contains parameters
     */
    private function input(array $field): string
    {
        $return = '';
        if (isset($field['label'])) {
            $return .= $this->label($field);
        }
        $placeholder = isset($field['placeholder']) && \is_string($field['placeholder'])
            ? " placeholder=\"{$field['placeholder']}\""
            : '';
        $return .= str_replace("\n", '', <<<HTML
<input type="{$field['type']}" id="{$field['id']}" name="{$field['name']}" value="{$field['value']}"
{$placeholder} {$this->getRequired($field)}{$this->getClass($field)}>
HTML);
        return $return;
    }

    /**
     * Return type of the input, based on the name.
     *
     * @param string $name Name of the input
     */
    private function getType(string $name): string
    {
        if (true === $this->config->get('TYPE_DETECTION')) {
            if (\in_array($name, $this->types, true)) {
                return $name;
            }
            if (isset(explode('_', $name)[1]) && 'at' === explode('_', $name)[1]) {
                return 'date';
            }
        }

        return 'text';
    }

    /**
     * Return if the field is required.
     *
     * @param string[]|bool[] $field
     */
    private function getRequired(array $field): string
    {
        return isset($field['required']) && false === $field['required'] ? '' : 'required=""';
    }

    /**
     * Return the class of the field.
     *
     * @param string[] $field
     */
    private function getClass(array $field): string
    {
        return isset($field['class']) && \is_string($field['class']) ? " class=\"{$field['class']}\"" : '';
    }

    private function getGeneratedFields(): string
    {
        $form = '';
        foreach ($this->fields as $key => $field) {
            if ('select' === $field['type']) {
                $form = $form . $this->select($field);
            } else {
                $form = $form . $this->input($field);
            }
            if (($key + 1) < \count($this->fields)) {
                $form .= "\n";
            }
        }
        return $form;
    }
}
