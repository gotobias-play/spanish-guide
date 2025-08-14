<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\AIWritingAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class AIWritingAnalysisServiceTest extends TestCase
{
    private AIWritingAnalysisService $aiService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aiService = new AIWritingAnalysisService();
        Cache::flush(); // Clear cache for consistent testing
    }

    /**
     * Test basic writing analysis functionality
     */
    public function test_basic_writing_analysis(): void
    {
        $text = "Hello, my name is John. I am learning English. I like to read books and watch movies.";
        $result = $this->aiService->analyzeWriting($text, 'intermediate');

        // Test basic structure
        $this->assertIsArray($result);
        $this->assertArrayHasKey('overall_score', $result);
        $this->assertArrayHasKey('grammar_analysis', $result);
        $this->assertArrayHasKey('vocabulary_analysis', $result);
        $this->assertArrayHasKey('structure_analysis', $result);
        $this->assertArrayHasKey('fluency_analysis', $result);
        $this->assertArrayHasKey('coherence_analysis', $result);
        $this->assertArrayHasKey('style_analysis', $result);
        $this->assertArrayHasKey('estimated_cefr_level', $result);
        $this->assertArrayHasKey('processing_time', $result);

        // Test score ranges
        $this->assertGreaterThanOrEqual(0, $result['overall_score']);
        $this->assertLessThanOrEqual(100, $result['overall_score']);

        // Test CEFR level format
        $this->assertMatchesRegularExpression('/^[ABC][12]$/', $result['estimated_cefr_level']);
    }

    /**
     * Test grammar analysis component
     */
    public function test_grammar_analysis(): void
    {
        $text = "He are a good student. She have many books."; // Intentional grammar errors
        $result = $this->aiService->analyzeWriting($text, 'beginner');

        $grammarAnalysis = $result['grammar_analysis'];
        
        $this->assertArrayHasKey('score', $grammarAnalysis);
        $this->assertArrayHasKey('errors_found', $grammarAnalysis);
        $this->assertArrayHasKey('error_rate', $grammarAnalysis);
        $this->assertArrayHasKey('corrections', $grammarAnalysis);
        $this->assertArrayHasKey('error_types', $grammarAnalysis);
        $this->assertArrayHasKey('complexity_score', $grammarAnalysis);

        // Grammar score should be lower due to errors
        $this->assertLessThan(80, $grammarAnalysis['score']);
        $this->assertIsNumeric($grammarAnalysis['error_rate']);
    }

    /**
     * Test vocabulary analysis component
     */
    public function test_vocabulary_analysis(): void
    {
        $text = "The sophisticated entrepreneur demonstrated remarkable perseverance and sagacity.";
        $result = $this->aiService->analyzeWriting($text, 'advanced');

        $vocabularyAnalysis = $result['vocabulary_analysis'];
        
        $this->assertArrayHasKey('score', $vocabularyAnalysis);
        $this->assertArrayHasKey('total_words', $vocabularyAnalysis);
        $this->assertArrayHasKey('unique_words', $vocabularyAnalysis);
        $this->assertArrayHasKey('diversity_ratio', $vocabularyAnalysis);
        $this->assertArrayHasKey('level_breakdown', $vocabularyAnalysis);
        $this->assertArrayHasKey('appropriateness_score', $vocabularyAnalysis);

        $this->assertGreaterThan(0, $vocabularyAnalysis['total_words']);
        $this->assertGreaterThan(0, $vocabularyAnalysis['unique_words']);
        $this->assertLessThanOrEqual(1, $vocabularyAnalysis['diversity_ratio']);
    }

    /**
     * Test structure analysis component
     */
    public function test_structure_analysis(): void
    {
        $text = "First paragraph with multiple sentences. This is another sentence. \n\nSecond paragraph here. It contains different ideas.";
        $result = $this->aiService->analyzeWriting($text, 'intermediate');

        $structureAnalysis = $result['structure_analysis'];
        
        $this->assertArrayHasKey('score', $structureAnalysis);
        $this->assertArrayHasKey('paragraph_count', $structureAnalysis);
        $this->assertArrayHasKey('sentence_count', $structureAnalysis);
        $this->assertArrayHasKey('avg_sentences_per_paragraph', $structureAnalysis);
        $this->assertArrayHasKey('organization_analysis', $structureAnalysis);

        $this->assertGreaterThan(1, $structureAnalysis['paragraph_count']);
        $this->assertGreaterThan(1, $structureAnalysis['sentence_count']);
    }

    /**
     * Test fluency analysis component
     */
    public function test_fluency_analysis(): void
    {
        $text = "Reading comprehension improves through consistent practice. Students benefit from varied materials.";
        $result = $this->aiService->analyzeWriting($text, 'intermediate');

        $fluencyAnalysis = $result['fluency_analysis'];
        
        $this->assertArrayHasKey('score', $fluencyAnalysis);
        $this->assertArrayHasKey('readability_score', $fluencyAnalysis);
        $this->assertArrayHasKey('avg_words_per_sentence', $fluencyAnalysis);
        $this->assertArrayHasKey('avg_syllables_per_word', $fluencyAnalysis);
        $this->assertArrayHasKey('reading_level', $fluencyAnalysis);

        $this->assertIsNumeric($fluencyAnalysis['avg_words_per_sentence']);
        $this->assertGreaterThan(0, $fluencyAnalysis['avg_syllables_per_word']);
        $this->assertIsString($fluencyAnalysis['reading_level']);
    }

    /**
     * Test coherence analysis component
     */
    public function test_coherence_analysis(): void
    {
        $text = "Education is important. Furthermore, it opens many opportunities. However, it requires dedication.";
        $result = $this->aiService->analyzeWriting($text, 'intermediate');

        $coherenceAnalysis = $result['coherence_analysis'];
        
        $this->assertArrayHasKey('score', $coherenceAnalysis);
        $this->assertArrayHasKey('topic_consistency', $coherenceAnalysis);
        $this->assertArrayHasKey('reference_clarity', $coherenceAnalysis);
        $this->assertArrayHasKey('logical_connections', $coherenceAnalysis);

        $this->assertIsNumeric($coherenceAnalysis['topic_consistency']);
        $this->assertIsNumeric($coherenceAnalysis['reference_clarity']);
        $this->assertIsNumeric($coherenceAnalysis['logical_connections']);
    }

    /**
     * Test style analysis component
     */
    public function test_style_analysis(): void
    {
        $text = "The research methodology employs quantitative analysis. Results indicate significant correlations.";
        $result = $this->aiService->analyzeWriting($text, 'advanced');

        $styleAnalysis = $result['style_analysis'];
        
        $this->assertArrayHasKey('score', $styleAnalysis);
        $this->assertArrayHasKey('tone_analysis', $styleAnalysis);
        $this->assertArrayHasKey('structure_variety', $styleAnalysis);
        $this->assertArrayHasKey('word_choice_sophistication', $styleAnalysis);

        $this->assertIsArray($styleAnalysis['tone_analysis']);
        $this->assertIsNumeric($styleAnalysis['structure_variety']);
        $this->assertIsNumeric($styleAnalysis['word_choice_sophistication']);
    }

    /**
     * Test CEFR level estimation accuracy
     */
    public function test_cefr_level_estimation(): void
    {
        // Basic text (A1-A2 level)
        $basicText = "I am happy. The cat is black. I like apples.";
        $basicResult = $this->aiService->analyzeWriting($basicText, 'beginner');
        $this->assertContains($basicResult['estimated_cefr_level'], ['A1', 'A2', 'B1']);

        // Advanced text (B2-C2 level)
        $advancedText = "The multifaceted implications of globalization necessitate comprehensive analysis of socioeconomic paradigms.";
        $advancedResult = $this->aiService->analyzeWriting($advancedText, 'advanced');
        $this->assertContains($advancedResult['estimated_cefr_level'], ['B1', 'B2', 'C1', 'C2']);
    }

    /**
     * Test caching functionality
     */
    public function test_caching_functionality(): void
    {
        $text = "This is a test for caching functionality.";
        
        // First analysis - should be cached
        $firstResult = $this->aiService->analyzeWriting($text, 'intermediate');
        $firstProcessingTime = $firstResult['processing_time'];
        
        // Second analysis - should be from cache (faster)
        $secondResult = $this->aiService->analyzeWriting($text, 'intermediate');
        $secondProcessingTime = $secondResult['processing_time'];
        
        // Results should be identical
        $this->assertEquals($firstResult['overall_score'], $secondResult['overall_score']);
        $this->assertEquals($firstResult['estimated_cefr_level'], $secondResult['estimated_cefr_level']);
        
        // Second call should be faster (from cache)
        $this->assertLessThanOrEqual($firstProcessingTime, $secondProcessingTime);
    }

    /**
     * Test input validation and edge cases
     */
    public function test_edge_cases(): void
    {
        // Very short text
        $shortText = "Hello.";
        $shortResult = $this->aiService->analyzeWriting($shortText, 'beginner');
        $this->assertIsArray($shortResult);
        $this->assertArrayHasKey('overall_score', $shortResult);

        // Text with only punctuation
        $punctuationText = "!!! ??? ... !!!";
        $punctuationResult = $this->aiService->analyzeWriting($punctuationText, 'beginner');
        $this->assertIsArray($punctuationResult);

        // Mixed case and special characters
        $mixedText = "HeLLo WoRLD!!! This is a TEST... 123 #hashtag @mention";
        $mixedResult = $this->aiService->analyzeWriting($mixedText, 'intermediate');
        $this->assertIsArray($mixedResult);
        $this->assertGreaterThan(0, $mixedResult['overall_score']);
    }

    /**
     * Test performance with longer texts
     */
    public function test_performance_with_long_text(): void
    {
        $longText = str_repeat("This is a sentence for testing performance with longer texts. ", 50);
        
        $startTime = microtime(true);
        $result = $this->aiService->analyzeWriting($longText, 'intermediate');
        $endTime = microtime(true);
        
        $actualProcessingTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        // Analysis should complete within reasonable time (5 seconds)
        $this->assertLessThan(5000, $actualProcessingTime);
        $this->assertIsArray($result);
        $this->assertGreaterThan(0, $result['overall_score']);
    }

    /**
     * Test different level inputs
     */
    public function test_different_levels(): void
    {
        $text = "The student is studying English grammar and vocabulary systematically.";
        
        $levels = ['beginner', 'intermediate', 'advanced', 'proficient'];
        
        foreach ($levels as $level) {
            $result = $this->aiService->analyzeWriting($text, $level);
            $this->assertIsArray($result);
            $this->assertArrayHasKey('overall_score', $result);
            $this->assertGreaterThan(0, $result['overall_score']);
        }
    }

    /**
     * Test feedback generation components
     */
    public function test_feedback_components(): void
    {
        $text = "I love learning new languages because they help me communicate with people.";
        $result = $this->aiService->analyzeWriting($text, 'intermediate');

        $this->assertArrayHasKey('detailed_feedback', $result);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertArrayHasKey('strengths', $result);
        $this->assertArrayHasKey('areas_for_improvement', $result);
        $this->assertArrayHasKey('next_learning_goals', $result);

        $this->assertIsArray($result['detailed_feedback']);
        $this->assertIsArray($result['suggestions']);
        $this->assertIsArray($result['strengths']);
        $this->assertIsArray($result['areas_for_improvement']);
        $this->assertIsArray($result['next_learning_goals']);
    }
}
