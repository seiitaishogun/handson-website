<?php namespace Test\Test\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTestTest extends Migration
{
    public function up()
    {
        Schema::table('test_test_', function($table)
        {
            $table->renameColumn('test', 'title');
        });
    }
    
    public function down()
    {
        Schema::table('test_test_', function($table)
        {
            $table->renameColumn('title', 'test');
        });
    }
}
