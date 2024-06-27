<?php namespace Test\Test\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateTestTestTest extends Migration
{
    public function up()
    {
        Schema::create('test_test_test', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('test_test_test');
    }
}
