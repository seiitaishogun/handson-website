<?php namespace Test\Test\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateTestTest extends Migration
{
    public function up()
    {
        Schema::create('test_test_', function($table)
        {
            $table->engine = 'InnoDB';
            $table->text('test');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('test_test_');
    }
}
