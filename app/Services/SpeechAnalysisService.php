<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SpeechAnalysisService
{
    private $pronunciationStandards;

    public function __construct()
    {
        $this->pronunciationStandards = $this->loadPronunciationStandards();
    }

    /**
     * Analyze pronunciation quality from audio input
     */
    public function analyzePronunciation($audioData, $targetText, $language = 'en')
    {
        try {
            // Simulate speech recognition and analysis
            $transcription = $this->transcribeAudio($audioData, $language);
            $pronunciationScore = $this->calculatePronunciationScore($transcription, $targetText);
            $phoneticAnalysis = $this->analyzePhonetics($transcription, $targetText);
            $fluencyMetrics = $this->calculateFluencyMetrics($audioData);

            return [
                'overall_score' => $pronunciationScore['overall'],
                'accuracy_score' => $pronunciationScore['accuracy'],
                'fluency_score' => $fluencyMetrics['fluency'],
                'clarity_score' => $fluencyMetrics['clarity'],
                'pace_score' => $fluencyMetrics['pace'],
                'transcription' => $transcription,
                'target_text' => $targetText,
                'phonetic_analysis' => $phoneticAnalysis,
                'detailed_feedback' => $this->generateDetailedFeedback($pronunciationScore, $phoneticAnalysis, $fluencyMetrics),
                'improvement_suggestions' => $this->generateImprovementSuggestions($phoneticAnalysis),
                'confidence_level' => $this->calculateConfidenceLevel($pronunciationScore),
                'analysis_timestamp' => now()->toISOString(),
            ];
        } catch (\Exception $e) {
            Log::error('Speech analysis failed', ['error' => $e->getMessage()]);
            throw new \Exception('Speech analysis failed: ' . $e->getMessage());
        }
    }

    /**
     * Simulate speech-to-text transcription
     */
    private function transcribeAudio($audioData, $language)
    {
        // Simulate API call to speech recognition service
        // In production, this would call Google Speech-to-Text, Azure Speech, or similar
        
        // For simulation, we'll use text similarity with common mispronunciations
        $commonTexts = [
            'Hello, how are you today?' => 'Hello, how are you today?',
            'I am learning English' => 'I am learning English',
            'The weather is very nice' => 'The weather is very nice',
            'She sells seashells by the seashore' => 'She sells seashells by the seashore',
            'Red leather, yellow leather' => 'Red leather, yellow leather',
            'Peter Piper picked a peck of pickled peppers' => 'Peter Piper picked a peck of pickled peppers',
        ];

        // Simulate varying accuracy based on complexity
        $simulatedVariations = [
            'Hello, how are you today?' => ['Hello, how are you today?', 'Hellow, how are you today?', 'Hello, how are yu today?'],
            'I am learning English' => ['I am learning English', 'I am learning Inglish', 'I am larning English'],
            'The weather is very nice' => ['The weather is very nice', 'The vether is very nice', 'The weather is wery nice'],
        ];

        // Return simulated transcription with realistic variations
        $audioHash = md5($audioData);
        $variation = intval(substr($audioHash, 0, 1), 16) % 3;
        
        foreach ($simulatedVariations as $target => $variations) {
            if (strlen($audioData) > 100) { // Simulate matching based on audio length
                return $variations[$variation] ?? $target;
            }
        }

        return 'I am practicing my pronunciation';
    }

    /**
     * Calculate pronunciation accuracy score
     */
    private function calculatePronunciationScore($transcription, $targetText)
    {
        $transcription = strtolower(trim($transcription));
        $targetText = strtolower(trim($targetText));

        // Word-level accuracy
        $transcriptionWords = explode(' ', $transcription);
        $targetWords = explode(' ', $targetText);
        
        $correctWords = 0;
        $totalWords = count($targetWords);

        foreach ($targetWords as $index => $targetWord) {
            if (isset($transcriptionWords[$index])) {
                $similarity = $this->calculateWordSimilarity($transcriptionWords[$index], $targetWord);
                if ($similarity > 0.8) {
                    $correctWords++;
                }
            }
        }

        $wordAccuracy = $totalWords > 0 ? ($correctWords / $totalWords) * 100 : 0;

        // Character-level accuracy for overall score
        $levenshteinDistance = levenshtein($transcription, $targetText);
        $maxLength = max(strlen($transcription), strlen($targetText));
        $characterAccuracy = $maxLength > 0 ? (1 - ($levenshteinDistance / $maxLength)) * 100 : 0;

        // Phonetic accuracy simulation
        $phoneticAccuracy = $this->calculatePhoneticAccuracy($transcription, $targetText);

        return [
            'overall' => round(($wordAccuracy * 0.4 + $characterAccuracy * 0.3 + $phoneticAccuracy * 0.3), 1),
            'accuracy' => round($wordAccuracy, 1),
            'character_accuracy' => round($characterAccuracy, 1),
            'phonetic_accuracy' => round($phoneticAccuracy, 1),
        ];
    }

    /**
     * Analyze phonetic patterns and common errors
     */
    private function analyzePhonetics($transcription, $targetText)
    {
        $phoneticErrors = [];
        $strengths = [];
        
        // Common pronunciation patterns for Spanish speakers learning English
        $commonErrors = [
            '/th/' => ['d', 't'], // "think" -> "dink" or "tink"
            '/v/' => ['b'], // "very" -> "bery"
            '/ʃ/' => ['s'], // "ship" -> "sip"
            '/dʒ/' => ['y'], // "jump" -> "yump"
            '/r/' => ['rr'], // English R vs Spanish RR
        ];

        // Detect common phonetic errors
        foreach ($commonErrors as $phoneme => $errorPatterns) {
            foreach ($errorPatterns as $error) {
                if ($this->detectPhoneticError($transcription, $targetText, $phoneme, $error)) {
                    $phoneticErrors[] = [
                        'phoneme' => $phoneme,
                        'error_type' => $error,
                        'description' => $this->getPhoneticErrorDescription($phoneme, $error),
                        'examples' => $this->getPhoneticExamples($phoneme),
                    ];
                }
            }
        }

        // Identify pronunciation strengths
        $strongPhonemes = $this->identifyStrongPhonemes($transcription, $targetText);
        foreach ($strongPhonemes as $phoneme) {
            $strengths[] = [
                'phoneme' => $phoneme,
                'description' => "Good pronunciation of {$phoneme} sounds",
            ];
        }

        return [
            'errors' => $phoneticErrors,
            'strengths' => $strengths,
            'difficulty_areas' => $this->identifyDifficultyAreas($phoneticErrors),
            'phonetic_pattern_score' => $this->calculatePhoneticPatternScore($phoneticErrors, $strengths),
        ];
    }

    /**
     * Calculate fluency metrics from audio characteristics
     */
    private function calculateFluencyMetrics($audioData)
    {
        // Simulate audio analysis metrics
        $audioLength = strlen($audioData);
        $simulatedDuration = ($audioLength / 1000) * 2; // Simulate duration in seconds

        // Simulate fluency characteristics
        $pauseCount = rand(1, 5);
        $speechRate = rand(120, 180); // words per minute
        $volumeConsistency = rand(70, 95);
        $pronunciationClarity = rand(75, 95);

        // Calculate fluency score
        $fluencyScore = $this->calculateFluencyScore($pauseCount, $speechRate, $simulatedDuration);
        $clarityScore = $pronunciationClarity;
        $paceScore = $this->calculatePaceScore($speechRate);

        return [
            'fluency' => round($fluencyScore, 1),
            'clarity' => round($clarityScore, 1),
            'pace' => round($paceScore, 1),
            'speech_rate' => $speechRate,
            'pause_count' => $pauseCount,
            'duration' => round($simulatedDuration, 2),
            'volume_consistency' => $volumeConsistency,
        ];
    }

    /**
     * Generate detailed feedback for pronunciation
     */
    private function generateDetailedFeedback($pronunciationScore, $phoneticAnalysis, $fluencyMetrics)
    {
        $feedback = [];
        $overallScore = $pronunciationScore['overall'];

        // Overall assessment
        if ($overallScore >= 90) {
            $feedback['overall'] = "Excellent pronunciation! Your English pronunciation is very clear and accurate.";
        } elseif ($overallScore >= 80) {
            $feedback['overall'] = "Good pronunciation with minor areas for improvement.";
        } elseif ($overallScore >= 70) {
            $feedback['overall'] = "Acceptable pronunciation. Focus on specific sounds to improve clarity.";
        } else {
            $feedback['overall'] = "Needs improvement. Consider practicing specific pronunciation exercises.";
        }

        // Accuracy feedback
        if ($pronunciationScore['accuracy'] < 70) {
            $feedback['accuracy'] = "Focus on word pronunciation accuracy. Practice individual words slowly.";
        } else {
            $feedback['accuracy'] = "Good word recognition and pronunciation accuracy.";
        }

        // Fluency feedback
        if ($fluencyMetrics['fluency'] < 70) {
            $feedback['fluency'] = "Work on speaking more smoothly with fewer hesitations.";
        } else {
            $feedback['fluency'] = "Good speech fluency and natural rhythm.";
        }

        // Phonetic feedback
        if (!empty($phoneticAnalysis['errors'])) {
            $feedback['phonetics'] = "Pay attention to specific sounds: " . 
                implode(', ', array_map(function($error) {
                    return $error['phoneme'];
                }, $phoneticAnalysis['errors']));
        } else {
            $feedback['phonetics'] = "Good phonetic accuracy across different sounds.";
        }

        return $feedback;
    }

    /**
     * Generate specific improvement suggestions
     */
    private function generateImprovementSuggestions($phoneticAnalysis)
    {
        $suggestions = [];

        // General suggestions
        $suggestions[] = [
            'category' => 'General',
            'suggestion' => 'Practice speaking slowly and clearly, focusing on each sound.',
            'exercises' => ['Mirror practice', 'Recording and playback', 'Tongue twisters'],
        ];

        // Phonetic-specific suggestions
        foreach ($phoneticAnalysis['errors'] as $error) {
            $suggestions[] = [
                'category' => 'Phonetic',
                'phoneme' => $error['phoneme'],
                'suggestion' => $error['description'],
                'exercises' => $this->getPhoneticExercises($error['phoneme']),
            ];
        }

        // Fluency suggestions
        $suggestions[] = [
            'category' => 'Fluency',
            'suggestion' => 'Practice reading aloud daily to improve rhythm and flow.',
            'exercises' => ['Read news articles', 'Practice dialogues', 'Shadow speaking'],
        ];

        return $suggestions;
    }

    /**
     * Calculate confidence level of analysis
     */
    private function calculateConfidenceLevel($pronunciationScore)
    {
        $accuracy = $pronunciationScore['accuracy'];
        $overall = $pronunciationScore['overall'];
        
        // Higher confidence for clear scores
        if ($overall >= 85 || $overall <= 60) {
            return rand(85, 95);
        } else {
            return rand(70, 85);
        }
    }

    // Helper methods

    private function loadPronunciationStandards()
    {
        return Cache::remember('pronunciation_standards', 3600, function () {
            return [
                'vowels' => ['æ', 'ɑ', 'ɔ', 'ɛ', 'ɪ', 'i', 'ʊ', 'u', 'ʌ', 'ə'],
                'consonants' => ['p', 'b', 't', 'd', 'k', 'g', 'f', 'v', 'θ', 'ð', 's', 'z', 'ʃ', 'ʒ', 'h', 'tʃ', 'dʒ', 'm', 'n', 'ŋ', 'l', 'r', 'j', 'w'],
                'common_errors' => [
                    'th_sounds' => ['θ', 'ð'],
                    'r_sounds' => ['r'],
                    'v_sounds' => ['v'],
                ],
            ];
        });
    }

    private function calculateWordSimilarity($word1, $word2)
    {
        $distance = levenshtein($word1, $word2);
        $maxLength = max(strlen($word1), strlen($word2));
        return $maxLength > 0 ? 1 - ($distance / $maxLength) : 1;
    }

    private function calculatePhoneticAccuracy($transcription, $targetText)
    {
        // Simulate phonetic analysis
        $commonPhonemes = ['th', 'v', 'r', 'sh', 'ch'];
        $accuracy = 0;
        $phoneticMatches = 0;
        
        foreach ($commonPhonemes as $phoneme) {
            if (strpos($targetText, $phoneme) !== false) {
                if (strpos($transcription, $phoneme) !== false) {
                    $phoneticMatches++;
                }
            }
        }
        
        return count($commonPhonemes) > 0 ? ($phoneticMatches / count($commonPhonemes)) * 100 : 85;
    }

    private function detectPhoneticError($transcription, $targetText, $phoneme, $error)
    {
        // Simplified phonetic error detection
        return strpos($targetText, $phoneme) !== false && strpos($transcription, $error) !== false;
    }

    private function getPhoneticErrorDescription($phoneme, $error)
    {
        $descriptions = [
            '/th/' => 'Practice the "th" sound by placing your tongue between your teeth',
            '/v/' => 'Make sure your bottom lip touches your top teeth for the "v" sound',
            '/ʃ/' => 'The "sh" sound requires rounded lips and tongue position',
            '/r/' => 'English "r" is pronounced without rolling the tongue',
        ];
        
        return $descriptions[$phoneme] ?? "Practice the {$phoneme} sound carefully";
    }

    private function getPhoneticExamples($phoneme)
    {
        $examples = [
            '/th/' => ['think', 'thank', 'this', 'that'],
            '/v/' => ['very', 'voice', 'love', 'live'],
            '/ʃ/' => ['ship', 'wash', 'nation', 'sure'],
            '/r/' => ['red', 'tree', 'car', 'better'],
        ];
        
        return $examples[$phoneme] ?? ['example'];
    }

    private function identifyStrongPhonemes($transcription, $targetText)
    {
        // Simulate identification of well-pronounced phonemes
        $strongPhonemes = [];
        $allPhonemes = ['/p/', '/b/', '/t/', '/d/', '/k/', '/g/', '/m/', '/n/'];
        
        foreach ($allPhonemes as $phoneme) {
            if (rand(0, 100) > 70) { // Simulate 30% chance of being strong
                $strongPhonemes[] = $phoneme;
            }
        }
        
        return $strongPhonemes;
    }

    private function identifyDifficultyAreas($phoneticErrors)
    {
        $difficulties = [];
        foreach ($phoneticErrors as $error) {
            $difficulties[] = $error['phoneme'];
        }
        return array_unique($difficulties);
    }

    private function calculatePhoneticPatternScore($errors, $strengths)
    {
        $errorCount = count($errors);
        $strengthCount = count($strengths);
        
        if ($errorCount + $strengthCount === 0) {
            return 75;
        }
        
        return round(($strengthCount / ($errorCount + $strengthCount)) * 100, 1);
    }

    private function calculateFluencyScore($pauseCount, $speechRate, $duration)
    {
        $pauseScore = max(0, 100 - ($pauseCount * 10));
        $rateScore = ($speechRate >= 120 && $speechRate <= 180) ? 100 : max(0, 100 - abs($speechRate - 150));
        
        return ($pauseScore + $rateScore) / 2;
    }

    private function calculatePaceScore($speechRate)
    {
        if ($speechRate >= 140 && $speechRate <= 160) {
            return 100;
        } elseif ($speechRate >= 120 && $speechRate <= 180) {
            return 85;
        } else {
            return max(0, 100 - abs($speechRate - 150));
        }
    }

    private function getPhoneticExercises($phoneme)
    {
        $exercises = [
            '/th/' => ['Tongue twisters with "th"', 'Mirror practice for tongue placement', 'Minimal pairs (think/sink)'],
            '/v/' => ['Lip positioning exercises', 'Contrast with "b" sound', 'Word repetition drills'],
            '/r/' => ['Tongue tip exercises', 'American R practice', 'Word-final R practice'],
        ];
        
        return $exercises[$phoneme] ?? ['General pronunciation practice'];
    }
}