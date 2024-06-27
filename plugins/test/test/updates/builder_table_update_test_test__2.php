<?php namespace Test\Test\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTestTest2 extends Migration
{
    public function up()
    {
        Schema::table('test_test_', function($table)
        {
            $table->integer('id');
        });
    }
    
    public function down()
    {
        Schema::table('test_test_', function($table)
        {
            $table->dropColumn('id');
        });
    }
}
