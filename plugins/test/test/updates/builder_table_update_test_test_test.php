<?php namespace Test\Test\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTestTestTest extends Migration
{
    public function up()
    {
        Schema::table('test_test_test', function($table)
        {
            $table->string('title');
        });
    }
    
    public function down()
    {
        Schema::table('test_test_test', function($table)
        {
            $table->dropColumn('title');
        });
    }
}
