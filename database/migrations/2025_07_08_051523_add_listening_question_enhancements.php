<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // For matching questions
            $table->json('matching_pairs')->nullable();
            // Structure: [
            //   ['left' => 'Question 1', 'right' => 'Answer A'],
            //   ['left' => 'Question 2', 'right' => 'Answer B']
            // ]
            
            // For form completion
            $table->json('form_structure')->nullable();
            // Structure: {
            //   'title': 'Student Registration Form',
            //   'fields': [
            //     {'label': 'Name', 'blank_id': 1, 'answer': 'John Smith'},
            //     {'label': 'Age', 'blank_id': 2, 'answer': '25'}
            //   ]
            // }
            
            // For plan/map/diagram
            $table->json('diagram_hotspots')->nullable();
            // Structure: [
            //   {'id': 1, 'x': 150, 'y': 200, 'label': 'A', 'answer': 'Reception'},
            //   {'id': 2, 'x': 300, 'y': 250, 'label': 'B', 'answer': 'Library'}
            // ]
            
            // Template reference
            $table->string('template_type')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn([
                'matching_pairs',
                'form_structure', 
                'diagram_hotspots',
                'template_type'
            ]);
        });
    }
};