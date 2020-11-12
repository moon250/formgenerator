<?php

namespace Tests;

use FormGenerator\FormConfig;
use FormGenerator\FormGenerator;
use FormGenerator\Types\SelectType;
use PHPUnit\Framework\TestCase;

class FormGeneratorTest extends TestCase
{
    private FormGenerator $form;

    public function setUp(): void
    {
        $this->form = new FormGenerator();
    }

    public function testAddSimpleField()
    {
        $this->form->add('name');
        $html = '<input type="text" id="field-name" name="name" value="" required="">';

        $this->assertSame($html, $this->form->generate());
    }

    public function testAddMultipleFields()
    {
        $this->form
            ->add('username')
            ->add('name');
        $html = '<input type="text" id="field-username" name="username" value="" required="">
<input type="text" id="field-name" name="name" value="" required="">';
        $this->assertSame($html, $this->form->generate());
    }

    public function testAddFieldWithSpecifiedValue()
    {
        $this->form->add('test', 'email');
        $html = '<input type="email" id="field-test" name="test" value="" required="">';
        $this->assertSame($html, $this->form->generate());
    }

    public function testAutoDetectionCreatedAtType()
    {
        $this->form->add('created_at');
        $html = '<input type="date" id="field-created_at" name="created_at" value="" required="">';
        $this->assertSame($html, $this->form->generate());
    }

    public function testFtieldWithCustomType()
    {
        $this->form->add('users', new SelectType(['jean', 'jane']));
        $html = <<<HTML
<select id="field-users" name="users" required="">
    <option value="0">jean</option>
    <option value="1">jane</option>
</select>
HTML;
        $this->assertSame($html, $this->form->generate());
    }

    public function testGenerateClearGeneratedFields()
    {
        $form1 = $this->form->add('username')->generate();
        $html = '<input type="text" id="field-username" name="username" value="" required="">';
        $form2 = $this->form->add('user')->generate();
        $html2 = '<input type="text" id="field-user" name="user" value="" required="">';
        $this->assertSame($html, $form1);
        $this->assertSame($html2, $form2);
    }

    public function testDetectNameForUseItInType()
    {
        $form1 = $this->form->add('email')->generate();
        $html = '<input type="email" id="field-email" name="email" value="" required="">';
        $form2 = $this->form->add('date')->generate();
        $html2 = '<input type="date" id="field-date" name="date" value="" required="">';
        $this->assertSame($html, $form1);
        $this->assertSame($html2, $form2);
    }

    public function testConfigureOneOption()
    {
        $config = new FormConfig();
        $config->set('TYPE_DETECTION', false);
        $form = (new FormGenerator($config))->add('email')->generate();
        $html = '<input type="text" id="field-email" name="email" value="" required="">';
        $this->assertSame($html, $form);
    }

    public function testAddFieldWithOption()
    {
        $form = $this->form->add('username', null, [
            'label'       => "Nom d'utilisateur"
        ])->generate();
        $html = <<<HTML
<label for="field-username">Nom d'utilisateur</label>
<input type="text" id="field-username" name="username" value="" required="">
HTML;
        $this->assertSame($html, $form);
    }

    public function testAddFieldWithMultipleOptions()
    {
        $form = $this->form->add('username', null, [
            'label'       => "Nom d'utilisateur",
            'placeholder' => 'Un placeholder'
        ])->generate();
        $html = <<<HTML
<label for="field-username">Nom d'utilisateur</label>
<input type="text" id="field-username" name="username" value="" placeholder="Un placeholder" required="">
HTML;
        $this->assertSame($html, $form);
    }

    public function testRequiredField()
    {
        $this->form->add('name', null, [
            'required' => true
        ]);
        $html = '<input type="text" id="field-name" name="name" value="" required="">';
        $this->assertSame($html, $this->form->generate());
    }

    public function testAddClass()
    {
        $this->form->add('username', null, [
            'class' => 'test'
        ]);
        $html = '<input type="text" id="field-username" name="username" value="" required="" class="test">';
        $this->assertSame($html, $this->form->generate());
    }
}
