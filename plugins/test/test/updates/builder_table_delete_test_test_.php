<?php namespace Test\Test\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableDeleteTestTest extends Migration
{
    public function up()
    {
        Schema::dropIfExists('test_test_');
    }
    
    public function down()
    {
        Schema::create('test_test_', function($table)
        {
            $table->engine = 'InnoDB';
            $table->text('title');
            $table->integer('id');
        });
    }
}
