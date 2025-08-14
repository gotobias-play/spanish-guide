<template>
  <div class="card p-4 mt-8">
    <div v-if="loading" class="text-center text-lg">Loading quiz...</div>
    <div v-else-if="error" class="text-center text-lg text-red-500">Error: {{ error }}</div>
    
    <template v-else-if="quiz">
      <h3 class="text-xl font-bold text-center mb-4">{{ quiz.title }}</h3>
      
      <div v-if="!quizCompleted && currentQuestion">
        <div class="mb-4">
          <span class="text-sm text-gray-500">Question {{ currentQuestionIndex + 1 }} of {{ quiz.questions.length }}</span>
        </div>
        
        <p class="text-lg font-semibold mb-4">{{ currentQuestion.question_text }}</p>
        
        <!-- Multiple Choice Questions -->
        <div v-if="currentQuestion.question_type === 'multiple_choice'" class="flex flex-wrap gap-2 justify-center mb-4">
          <button 
            v-for="option in currentQuestion.options" 
            :key="option.id"
            @click="checkAnswer(option)"
            :disabled="answered"
            class="interactive-btn"
            :class="{ 
              'bg-green-500': answered && option.is_correct,
              'bg-red-500': answered && selectedOption?.id === option.id && !option.is_correct,
              'opacity-50': answered && !option.is_correct && selectedOption?.id !== option.id
            }"
          >
            {{ option.option_text }}
          </button>
        </div>
        
        <!-- Fill in the blank Questions -->
        <div v-else-if="currentQuestion.question_type === 'fill_in_the_blank'" class="mb-4">
          <input 
            v-model="fillInAnswer"
            @keyup.enter="checkFillInAnswer"
            :disabled="answered"
            type="text" 
            class="w-full p-3 border rounded-lg text-center text-lg"
            placeholder="Type your answer here..."
          >
          <button 
            @click="checkFillInAnswer"
            :disabled="answered || !fillInAnswer.trim()"
            class="interactive-btn mt-2 w-full"
          >
            Submit Answer
          </button>
        </div>

        <!-- Image-based Questions -->
        <div v-else-if="currentQuestion.question_type === 'image_based'" class="mb-4">
          <div v-if="currentQuestion.image_url" class="mb-4 text-center">
            <img :src="currentQuestion.image_url" :alt="currentQuestion.question_text" 
                 class="max-w-full h-auto rounded-lg shadow-md mx-auto" style="max-height: 300px;">
          </div>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div 
              v-for="option in currentQuestion.options" 
              :key="option.id"
              @click="checkAnswer(option)"
              :class="{ 
                'ring-4 ring-green-500': answered && option.is_correct,
                'ring-4 ring-red-500': answered && selectedOption?.id === option.id && !option.is_correct,
                'opacity-50': answered && !option.is_correct && selectedOption?.id !== option.id,
                'cursor-pointer hover:shadow-lg transform hover:scale-105': !answered
              }"
              class="border-2 border-gray-200 rounded-lg p-4 text-center transition-all duration-200"
            >
              <img v-if="option.image_url" :src="option.image_url" :alt="option.option_text" 
                   class="w-full h-24 object-cover rounded mb-2">
              <p class="text-sm font-medium">{{ option.option_text }}</p>
            </div>
          </div>
        </div>

        <!-- Audio-based Questions -->
        <div v-else-if="currentQuestion.question_type === 'audio_based'" class="mb-4">
          <div v-if="currentQuestion.audio_url" class="mb-4 text-center">
            <div class="bg-blue-50 p-4 rounded-lg mb-4">
              <div class="flex items-center justify-center mb-3">
                <svg class="w-8 h-8 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.617.783l-4.288-3.425H2a1 1 0 01-1-1V7.64a1 1 0 011-.998h2.095l4.288-3.425a1 1 0 011 .859zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.983 5.983 0 01-1.757 4.243 1 1 0 01-1.415-1.415A3.984 3.984 0 0013 10a3.984 3.984 0 00-1.172-2.828 1 1 0 010-1.415z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-blue-600 font-medium">Listen and choose the correct answer</span>
              </div>
              <audio controls class="w-full">
                <source :src="currentQuestion.audio_url" type="audio/mpeg">
                <source :src="currentQuestion.audio_url" type="audio/wav">
                Your browser does not support the audio element.
              </audio>
            </div>
          </div>
          <div class="flex flex-wrap gap-2 justify-center">
            <button 
              v-for="option in currentQuestion.options" 
              :key="option.id"
              @click="checkAnswer(option)"
              :disabled="answered"
              class="interactive-btn"
              :class="{ 
                'bg-green-500': answered && option.is_correct,
                'bg-red-500': answered && selectedOption?.id === option.id && !option.is_correct,
                'opacity-50': answered && !option.is_correct && selectedOption?.id !== option.id
              }"
            >
              {{ option.option_text }}
            </button>
          </div>
        </div>

        <!-- Drag and Drop Questions -->
        <div v-else-if="currentQuestion.question_type === 'drag_and_drop'" class="mb-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Draggable items -->
            <div>
              <h4 class="font-semibold mb-3 text-center">Drag items</h4>
              <div class="space-y-2">
                <div
                  v-for="option in dragItems"
                  :key="option.id"
                  :draggable="!answered"
                  @dragstart="onDragStart($event, option)"
                  @dragend="onDragEnd"
                  :class="{
                    'cursor-move hover:shadow-lg': !answered,
                    'cursor-not-allowed opacity-50': answered,
                    'bg-green-100': answered && dragAnswers[option.id] === option.is_correct,
                    'bg-red-100': answered && dragAnswers[option.id] === false
                  }"
                  class="bg-blue-100 p-3 rounded-lg border-2 border-blue-200 text-center transition-all duration-200"
                >
                  {{ option.option_text }}
                </div>
              </div>
            </div>
            
            <!-- Drop zones -->
            <div>
              <h4 class="font-semibold mb-3 text-center">Drop zones</h4>
              <div class="space-y-2">
                <div
                  v-for="zone in dropZones"
                  :key="zone.id"
                  @drop="onDrop($event, zone)"
                  @dragover.prevent
                  @dragenter.prevent
                  :class="{
                    'bg-green-100 border-green-300': answered && droppedItems[zone.id]?.is_correct,
                    'bg-red-100 border-red-300': answered && droppedItems[zone.id] && !droppedItems[zone.id].is_correct,
                    'bg-gray-100 border-gray-300': !droppedItems[zone.id]
                  }"
                  class="min-h-16 p-3 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center transition-all duration-200"
                >
                  <span v-if="droppedItems[zone.id]" class="font-medium">
                    {{ droppedItems[zone.id].option_text }}
                  </span>
                  <span v-else class="text-gray-500">
                    {{ zone.label }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          
          <button 
            @click="checkDragDropAnswer"
            :disabled="answered || !allDropZonesFilled"
            class="interactive-btn mt-4 w-full"
          >
            Submit Answer
          </button>
        </div>

        <!-- Matching Questions -->
        <div v-else-if="currentQuestion.question_type === 'matching'" class="mb-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Left column -->
            <div>
              <h4 class="font-semibold mb-3 text-center">Match items</h4>
              <div class="space-y-2">
                <div
                  v-for="item in leftColumnItems"
                  :key="item.id"
                  @click="selectMatchingItem(item, 'left')"
                  :class="{
                    'ring-2 ring-blue-500': selectedLeftItem?.id === item.id,
                    'bg-green-100': answered && matchingPairs[item.id]?.correct,
                    'bg-red-100': answered && matchingPairs[item.id] && !matchingPairs[item.id].correct,
                    'cursor-pointer hover:bg-blue-50': !answered
                  }"
                  class="p-3 border rounded-lg transition-all duration-200"
                >
                  {{ item.option_text }}
                </div>
              </div>
            </div>
            
            <!-- Right column -->
            <div>
              <h4 class="font-semibold mb-3 text-center">With these</h4>
              <div class="space-y-2">
                <div
                  v-for="item in rightColumnItems"
                  :key="item.id"
                  @click="selectMatchingItem(item, 'right')"
                  :class="{
                    'ring-2 ring-blue-500': selectedRightItem?.id === item.id,
                    'bg-green-100': answered && Object.values(matchingPairs).find(p => p.rightId === item.id)?.correct,
                    'bg-red-100': answered && Object.values(matchingPairs).find(p => p.rightId === item.id && !p.correct),
                    'cursor-pointer hover:bg-blue-50': !answered
                  }"
                  class="p-3 border rounded-lg transition-all duration-200"
                >
                  {{ item.option_text }}
                </div>
              </div>
            </div>
          </div>
          
          <div class="mt-4">
            <h5 class="font-medium mb-2">Your matches:</h5>
            <div class="bg-gray-50 p-3 rounded-lg min-h-16">
              <div v-for="(pair, leftId) in matchingPairs" :key="leftId" class="flex justify-between items-center mb-1">
                <span>{{ getItemText(leftId, 'left') }}</span>
                <span class="mx-2">↔</span>
                <span>{{ getItemText(pair.rightId, 'right') }}</span>
                <button v-if="!answered" @click="removePair(leftId)" class="ml-2 text-red-500 hover:text-red-700">×</button>
              </div>
              <p v-if="Object.keys(matchingPairs).length === 0" class="text-gray-500 text-center">
                Select items from both columns to create matches
              </p>
            </div>
          </div>
          
          <button 
            @click="checkMatchingAnswer"
            :disabled="answered || Object.keys(matchingPairs).length === 0"
            class="interactive-btn mt-4 w-full"
          >
            Submit Answer
          </button>
        </div>

        <!-- Ordering Questions -->
        <div v-else-if="currentQuestion.question_type === 'ordering'" class="mb-4">
          <p class="text-center text-gray-600 mb-4">Drag to reorder the items in the correct sequence:</p>
          <div class="space-y-2">
            <div
              v-for="(item, index) in orderingItems"
              :key="item.id"
              :draggable="!answered"
              @dragstart="onOrderDragStart($event, index)"
              @dragover.prevent
              @drop="onOrderDrop($event, index)"
              :class="{
                'cursor-move hover:shadow-lg': !answered,
                'cursor-not-allowed': answered,
                'bg-green-100 border-green-300': answered && item.position === index + 1,
                'bg-red-100 border-red-300': answered && item.position !== index + 1
              }"
              class="p-4 border-2 rounded-lg flex items-center justify-between transition-all duration-200"
            >
              <span>{{ index + 1 }}. {{ item.option_text }}</span>
              <div v-if="!answered" class="text-gray-400">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 6L7 3 7 6 3 6 3 9 7 9 7 12 10 9 13 12 13 9 17 9 17 6 13 6 13 3z"></path>
                </svg>
              </div>
            </div>
          </div>
          
          <button 
            @click="checkOrderingAnswer"
            :disabled="answered"
            class="interactive-btn mt-4 w-full"
          >
            Submit Answer
          </button>
        </div>
        
        <p v-if="feedback" class="feedback text-center mt-4" :class="feedbackClass">{{ feedback }}</p>
        
        <div class="text-center mt-4" v-if="showNextButton">
          <button @click="nextQuestion" class="interactive-btn">
            {{ currentQuestionIndex < quiz.questions.length - 1 ? 'Next Question' : 'Finish Quiz' }}
          </button>
        </div>
      </div>
      
      <div v-else-if="quizCompleted" class="text-center">
        <p class="text-2xl font-bold text-green-700 mb-4">Quiz Completed!</p>
        <p class="text-lg mb-2">You got {{ correctAnswers }} out of {{ quiz.questions.length }} correct.</p>
        <p class="text-lg mb-4">Score: {{ Math.round((correctAnswers / quiz.questions.length) * 100) }}%</p>
        
        <div class="mb-4">
          <h4 class="font-semibold mb-2">Review:</h4>
          <div v-for="(result, index) in questionResults" :key="index" class="text-left mb-2 p-2 rounded" 
               :class="result.correct ? 'bg-green-100' : 'bg-red-100'">
            <p class="font-medium">{{ result.question }}</p>
            <p class="text-sm">Your answer: {{ result.userAnswer }}</p>
            <p v-if="!result.correct" class="text-sm text-green-600">Correct answer: {{ result.correctAnswer }}</p>
          </div>
        </div>
        
        <button @click="restartQuiz" class="interactive-btn mt-4">Restart Quiz</button>
      </div>
    </template>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'DatabaseQuiz',
  props: {
    quizId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      quiz: null,
      loading: true,
      error: null,
      currentQuestionIndex: 0,
      correctAnswers: 0,
      feedback: '',
      feedbackClass: '',
      showNextButton: false,
      quizCompleted: false,
      answered: false,
      selectedOption: null,
      fillInAnswer: '',
      questionResults: [],
      
      // Advanced question type data
      dragItems: [],
      dropZones: [],
      droppedItems: {},
      dragAnswers: {},
      draggedItem: null,
      
      // Matching question data
      leftColumnItems: [],
      rightColumnItems: [],
      matchingPairs: {},
      selectedLeftItem: null,
      selectedRightItem: null,
      
      // Ordering question data
      orderingItems: [],
      draggedOrderIndex: null
    };
  },
  computed: {
    currentQuestion() {
      return this.quiz?.questions?.[this.currentQuestionIndex] || null;
    },
    
    allDropZonesFilled() {
      return this.dropZones.every(zone => this.droppedItems[zone.id]);
    }
  },
  async created() {
    await this.loadQuiz();
  },
  watch: {
    quizId: {
      immediate: true,
      async handler(newId) {
        if (newId) {
          await this.loadQuiz();
        }
      }
    },
    
    currentQuestion: {
      immediate: true,
      handler(newQuestion) {
        if (newQuestion) {
          this.initializeAdvancedQuestion();
        }
      }
    }
  },
  methods: {
    async loadQuiz() {
      try {
        this.loading = true;
        this.error = null;
        const response = await axios.get(`/api/public/quizzes/${this.quizId}`);
        this.quiz = response.data;
        
        // Shuffle questions for randomization
        if (this.quiz.questions) {
          this.quiz.questions = this.shuffleArray([...this.quiz.questions]);
          // Shuffle options for multiple choice questions
          this.quiz.questions.forEach(question => {
            if (question.question_type === 'multiple_choice' && question.options) {
              question.options = this.shuffleArray([...question.options]);
            }
          });
        }
        
        this.resetQuiz();
      } catch (err) {
        this.error = err.message || 'Failed to load quiz';
        console.error('Error loading quiz:', err);
      } finally {
        this.loading = false;
      }
    },
    
    shuffleArray(array) {
      const shuffled = [...array];
      for (let i = shuffled.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
      }
      return shuffled;
    },
    
    checkAnswer(option) {
      if (this.answered) return;
      
      this.answered = true;
      this.selectedOption = option;
      
      if (option.is_correct) {
        this.feedback = '¡Correcto!';
        this.feedbackClass = 'correct';
        this.correctAnswers++;
        this.questionResults.push({
          question: this.currentQuestion.question_text,
          userAnswer: option.option_text,
          correctAnswer: option.option_text,
          correct: true
        });
      } else {
        const correctOption = this.currentQuestion.options.find(opt => opt.is_correct);
        this.feedback = `Incorrecto. La respuesta correcta es "${correctOption.option_text}".`;
        this.feedbackClass = 'incorrect';
        this.questionResults.push({
          question: this.currentQuestion.question_text,
          userAnswer: option.option_text,
          correctAnswer: correctOption.option_text,
          correct: false
        });
      }
      
      this.showNextButton = true;
    },
    
    checkFillInAnswer() {
      if (this.answered || !this.fillInAnswer.trim()) return;
      
      this.answered = true;
      const correctOption = this.currentQuestion.options.find(opt => opt.is_correct);
      const userAnswer = this.fillInAnswer.trim().toLowerCase();
      const correctAnswer = correctOption.option_text.toLowerCase();
      
      if (userAnswer === correctAnswer) {
        this.feedback = '¡Correcto!';
        this.feedbackClass = 'correct';
        this.correctAnswers++;
        this.questionResults.push({
          question: this.currentQuestion.question_text,
          userAnswer: this.fillInAnswer.trim(),
          correctAnswer: correctOption.option_text,
          correct: true
        });
      } else {
        this.feedback = `Incorrecto. La respuesta correcta es "${correctOption.option_text}".`;
        this.feedbackClass = 'incorrect';
        this.questionResults.push({
          question: this.currentQuestion.question_text,
          userAnswer: this.fillInAnswer.trim(),
          correctAnswer: correctOption.option_text,
          correct: false
        });
      }
      
      this.showNextButton = true;
    },
    
    nextQuestion() {
      if (this.currentQuestionIndex < this.quiz.questions.length - 1) {
        this.currentQuestionIndex++;
        this.resetQuestionState();
      } else {
        this.completeQuiz();
      }
    },
    
    completeQuiz() {
      this.quizCompleted = true;
      const score = Math.round((this.correctAnswers / this.quiz.questions.length) * 100);
      
      // Emit quiz completion event with results
      this.$emit('quiz-completed', score, {
        quizId: this.quizId,
        quizTitle: this.quiz.title,
        totalQuestions: this.quiz.questions.length,
        correctAnswers: this.correctAnswers,
        score: score,
        results: this.questionResults
      });
    },
    
    // Initialize advanced question types
    initializeAdvancedQuestion() {
      const question = this.currentQuestion;
      if (!question) return;
      
      switch (question.question_type) {
        case 'drag_and_drop':
          this.initializeDragDrop();
          break;
        case 'matching':
          this.initializeMatching();
          break;
        case 'ordering':
          this.initializeOrdering();
          break;
      }
    },
    
    initializeDragDrop() {
      const config = this.currentQuestion.question_config || {};
      this.dragItems = [...this.currentQuestion.options];
      this.dropZones = config.dropZones || [
        { id: 'zone1', label: 'Drop here' },
        { id: 'zone2', label: 'Drop here' }
      ];
      this.droppedItems = {};
      this.dragAnswers = {};
    },
    
    initializeMatching() {
      const options = this.currentQuestion.options || [];
      this.leftColumnItems = options.filter(opt => opt.option_group === 'left');
      this.rightColumnItems = options.filter(opt => opt.option_group === 'right');
      this.matchingPairs = {};
      this.selectedLeftItem = null;
      this.selectedRightItem = null;
    },
    
    initializeOrdering() {
      this.orderingItems = [...(this.currentQuestion.options || [])].sort(() => Math.random() - 0.5);
    },
    
    // Drag and Drop methods
    onDragStart(event, item) {
      this.draggedItem = item;
      event.dataTransfer.effectAllowed = 'move';
    },
    
    onDragEnd() {
      this.draggedItem = null;
    },
    
    onDrop(event, zone) {
      if (this.draggedItem && !this.answered) {
        this.droppedItems[zone.id] = this.draggedItem;
        this.dragAnswers[this.draggedItem.id] = this.draggedItem.is_correct;
      }
    },
    
    checkDragDropAnswer() {
      if (this.answered) return;
      
      this.answered = true;
      const allCorrect = Object.values(this.dragAnswers).every(answer => answer);
      
      if (allCorrect) {
        this.feedback = '¡Correcto!';
        this.feedbackClass = 'correct';
        this.correctAnswers++;
      } else {
        this.feedback = 'Some items are in wrong places. Review the correct placements.';
        this.feedbackClass = 'incorrect';
      }
      
      this.questionResults.push({
        question: this.currentQuestion.question_text,
        userAnswer: 'Drag and drop arrangement',
        correctAnswer: 'Correct arrangement',
        correct: allCorrect
      });
      
      this.showNextButton = true;
    },
    
    // Matching methods
    selectMatchingItem(item, column) {
      if (this.answered) return;
      
      if (column === 'left') {
        this.selectedLeftItem = item;
      } else {
        this.selectedRightItem = item;
      }
      
      // Create pair if both items selected
      if (this.selectedLeftItem && this.selectedRightItem) {
        this.matchingPairs[this.selectedLeftItem.id] = {
          rightId: this.selectedRightItem.id,
          correct: this.checkMatchCorrectness(this.selectedLeftItem, this.selectedRightItem)
        };
        
        this.selectedLeftItem = null;
        this.selectedRightItem = null;
      }
    },
    
    checkMatchCorrectness(leftItem, rightItem) {
      // This would typically check against correct pairs stored in question_config
      const config = this.currentQuestion.question_config || {};
      const correctPairs = config.correctPairs || {};
      return correctPairs[leftItem.id] === rightItem.id;
    },
    
    removePair(leftId) {
      delete this.matchingPairs[leftId];
    },
    
    getItemText(itemId, column) {
      const items = column === 'left' ? this.leftColumnItems : this.rightColumnItems;
      return items.find(item => item.id == itemId)?.option_text || '';
    },
    
    checkMatchingAnswer() {
      if (this.answered) return;
      
      this.answered = true;
      const correctMatches = Object.values(this.matchingPairs).filter(pair => pair.correct).length;
      const totalPairs = this.leftColumnItems.length;
      const allCorrect = correctMatches === totalPairs;
      
      if (allCorrect) {
        this.feedback = '¡Correcto! All matches are perfect.';
        this.feedbackClass = 'correct';
        this.correctAnswers++;
      } else {
        this.feedback = `${correctMatches}/${totalPairs} matches correct. Review the incorrect pairs.`;
        this.feedbackClass = 'incorrect';
      }
      
      this.questionResults.push({
        question: this.currentQuestion.question_text,
        userAnswer: `${correctMatches}/${totalPairs} matches`,
        correctAnswer: `${totalPairs}/${totalPairs} matches`,
        correct: allCorrect
      });
      
      this.showNextButton = true;
    },
    
    // Ordering methods
    onOrderDragStart(event, index) {
      this.draggedOrderIndex = index;
      event.dataTransfer.effectAllowed = 'move';
    },
    
    onOrderDrop(event, targetIndex) {
      if (this.draggedOrderIndex !== null && this.draggedOrderIndex !== targetIndex && !this.answered) {
        const item = this.orderingItems.splice(this.draggedOrderIndex, 1)[0];
        this.orderingItems.splice(targetIndex, 0, item);
      }
      this.draggedOrderIndex = null;
    },
    
    checkOrderingAnswer() {
      if (this.answered) return;
      
      this.answered = true;
      const allCorrect = this.orderingItems.every((item, index) => item.position === index + 1);
      
      if (allCorrect) {
        this.feedback = '¡Correcto! Perfect sequence.';
        this.feedbackClass = 'correct';
        this.correctAnswers++;
      } else {
        this.feedback = 'Some items are out of order. Check the correct sequence.';
        this.feedbackClass = 'incorrect';
      }
      
      this.questionResults.push({
        question: this.currentQuestion.question_text,
        userAnswer: 'Custom ordering',
        correctAnswer: 'Correct sequence',
        correct: allCorrect
      });
      
      this.showNextButton = true;
    },
    
    resetQuestionState() {
      this.answered = false;
      this.selectedOption = null;
      this.fillInAnswer = '';
      this.feedback = '';
      this.feedbackClass = '';
      this.showNextButton = false;
      
      // Reset advanced question states
      this.droppedItems = {};
      this.dragAnswers = {};
      this.matchingPairs = {};
      this.selectedLeftItem = null;
      this.selectedRightItem = null;
      this.draggedItem = null;
      this.draggedOrderIndex = null;
    },
    
    resetQuiz() {
      this.currentQuestionIndex = 0;
      this.correctAnswers = 0;
      this.quizCompleted = false;
      this.questionResults = [];
      this.resetQuestionState();
    },
    
    restartQuiz() {
      // Reshuffle questions and options
      if (this.quiz.questions) {
        this.quiz.questions = this.shuffleArray([...this.quiz.questions]);
        this.quiz.questions.forEach(question => {
          if (question.question_type === 'multiple_choice' && question.options) {
            question.options = this.shuffleArray([...question.options]);
          }
        });
      }
      this.resetQuiz();
    }
  }
};
</script>