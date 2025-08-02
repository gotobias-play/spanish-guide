<template>
  <div>
    <h2 class="text-3xl font-bold text-center mb-6">Haciendo Preguntas: Palabras 'Wh'</h2>
    <p class="text-center text-lg mb-8">Aprende a pedir información con las palabras "Wh-". Haz clic en cada palabra para ver ejemplos.</p>

    <!-- Buttons -->
    <div class="flex justify-center mt-6 gap-2 md:gap-4 flex-wrap">
      <button 
        v-for="q in whQuestions" 
        :key="q.word" 
        @click="selectQuestion(q)" 
        class="interactive-btn"
        :class="{ 'selected': selectedQuestion && selectedQuestion.word === q.word }"
      >
        {{ q.word }}
      </button>
    </div>

    <!-- Display Area -->
    <div v-if="selectedQuestion" class="card max-w-3xl mx-auto mt-8" ref="displayArea">
      <p class="mb-4 text-lg text-center">
        Usa <strong class="text-blue-700">'{{ selectedQuestion.word }}'</strong> para preguntar sobre {{ selectedQuestion.use }}.
      </p>
      <div v-for="example in selectedQuestion.ex" :key="example.en" class="example">
        <p class="font-semibold text-gray-800">{{ example.en }}</p>
        <p class="pronunciation">{{ example.p }}</p>
        <p class="translation">{{ example.es }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import { gsap } from 'gsap';

export default {
  name: 'Questions',
  data() {
    return {
      selectedQuestion: null,
      whQuestions: [
        { word: 'Who', use: 'una PERSONA', ex: [
            { en: 'Who is your teacher?', es: '¿Quién es tu profesor?', p: '/jú is yor ti-cher/' },
            { en: 'Who are those people?', es: '¿Quiénes son esas personas?', p: '/jú ar dous pi-pol/' },
            { en: 'Who works here?', es: '¿Quién trabaja aquí?', p: '/jú werks jir/' }
        ]},
        { word: 'What', use: 'una COSA o IDEA', ex: [
            { en: 'What is your name?', es: '¿Cuál es tu nombre?', p: '/wót is yor néim/' },
            { en: 'What do you do?', es: '¿A qué te dedicas?', p: '/wót du yú du/' },
            { en: 'What time is it?', es: '¿Qué hora es?', p: '/wót táim is it/' }
        ]},
        { word: 'Where', use: 'un LUGAR', ex: [
            { en: 'Where do you live?', es: '¿Dónde vives?', p: '/wér du yú liv/' },
            { en: 'Where is the bathroom?', es: '¿Dónde está el baño?', p: '/wér is da báz-rum/' },
            { en: 'Where are you from?', es: '¿De dónde eres?', p: '/wér ar yú fróm/' }
        ]},
        { word: 'When', use: 'el TIEMPO', ex: [
            { en: 'When is your birthday?', es: '¿Cuándo es tu cumpleaños?', p: '/wén is yor bérz-dei/' },
            { en: 'When does the movie start?', es: '¿Cuándo empieza la película?', p: '/wén das da mú-vi start/' },
            { en: 'When do you study?', es: '¿Cuándo estudias?', p: '/wén du yú stá-di/' }
        ]},
        { word: 'Why', use: 'una RAZÓN', ex: [
            { en: 'Why are you sad?', es: '¿Por qué estás triste?', p: '/wái ar yú sad/' },
            { en: 'Why do you like pizza?', es: '¿Por qué te gusta la pizza?', p: '/wái du yú laik pit-sa/' },
            { en: 'Why is she running?', es: '¿Por qué corre ella?', p: '/wái is shi rá-ning/' }
        ]},
        { word: 'How', use: 'la MANERA o CONDICIÓN', ex: [
            { en: 'How are you?', es: '¿Cómo estás?', p: '/jáu ar yú/' },
            { en: 'How do you cook it?', es: '¿Cómo lo cocinas?', p: '/jáu du yú cuk it/' },
            { en: 'How much is it?', es: '¿Cuánto cuesta?', p: '/jáu mach is it/' }
        ]}
      ],
    };
  },
  methods: {
    selectQuestion(question) {
      this.selectedQuestion = question;
    },
  },
  watch: {
    selectedQuestion(newVal, oldVal) {
      if (newVal && newVal !== oldVal) {
        this.$nextTick(() => {
          gsap.from(this.$refs.displayArea, { 
            duration: 0.5, 
            opacity: 0, 
            y: 20,
            ease: 'power3.out'
          });
        });
      }
    }
  },
  mounted() {
    // Select the first question by default for a better initial view
    this.selectQuestion(this.whQuestions[0]);
    gsap.from(this.$el, { duration: 0.5, opacity: 0, y: 20 });
  },
};
</script>
