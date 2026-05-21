<?php

namespace Maatwebsite\Excel\Tests\Validators;

use Illuminate\Contracts\Validation\Factory;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Validators\RowValidator;

class RowValidatorTest extends TestCase
{
    /**
     * The RowValidator instance.
     */
    protected $validator;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new RowValidator(app(Factory::class));
    }

    public function test_format_rule_with_array_input(): void
    {
        $rules = ['rule1', 'rule2'];

        $result = $this->callPrivateMethod('formatRule', [$rules]);

        $this->assertEquals($rules, $result);
    }

    public function test_format_rule_with_object_input(): void
    {
        $rule = new \stdClass;

        $result = $this->callPrivateMethod('formatRule', [$rule]);

        $this->assertEquals($rule, $result);
    }

    public function test_format_rule_with_callable_input(): void
    {
        $rule = (fn () => 'callable');

        $result = $this->callPrivateMethod('formatRule', [$rule]);

        $this->assertEquals($rule, $result);
    }

    public function test_format_rule_with_required_without_all(): void
    {
        $rule = 'required_without_all:first_name,last_name';

        $result = $this->callPrivateMethod('formatRule', [$rule]);

        $this->assertEquals('required_without_all:*.first_name,*.last_name', $result);
    }

    public function test_format_rule_with_required_without(): void
    {
        $rule = 'required_without:first_name';

        $result = $this->callPrivateMethod('formatRule', [$rule]);

        $this->assertEquals('required_without:*.first_name', $result);
    }

    public function test_format_rule_with_string_input_not_matching_pattern(): void
    {
        $rule = 'rule';

        $result = $this->callPrivateMethod('formatRule', [$rule]);

        $this->assertEquals($rule, $result);
    }

    /**
     * Call a private function.
     *
     * @return mixed
     */
    public function callPrivateMethod(string $name, array $args)
    {
        $method = new \ReflectionMethod(RowValidator::class, $name);

        return $method->invokeArgs($this->validator, $args);
    }
}
