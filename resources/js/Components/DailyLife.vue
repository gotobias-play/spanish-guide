<template>
  <div>
    <h2 class="text-3xl font-bold text-center mb-6">Mi Día a Día: Presente Simple</h2>
    <p class="text-center text-lg mb-8">¡Practica oraciones positivas, negativas y preguntas!</p>
    <div class="card max-w-3xl mx-auto mb-8">
      <h3 class="text-xl font-bold mb-4 text-center">Constructor de Oraciones</h3>
      <div class="flex justify-center gap-4 mb-4">
        <button @click="sentence.type = 'positive'" :class="{ 'selected': sentence.type === 'positive' }" class="interactive-btn">Positivo (+)</button>
        <button @click="sentence.type = 'negative'" :class="{ 'selected': sentence.type === 'negative' }" class="interactive-btn">Negativo (-)</button>
      </div>
      <div class="text-center bg-gray-100 p-4 rounded-lg mb-4 h-24 flex flex-col items-center justify-center" ref="sentenceDisplay">
        <p v-if="sentence.subject && sentence.verb" class="text-lg font-semibold">
          <span class="font-bold text-blue-800">{{ sentence.subject }}</span>
          <span class="font-bold text-red-700 ml-2">{{ sentence.aux }}</span>
          <span class="font-bold text-green-800 ml-2">{{ sentence.finalVerb }}</span>.
        </p>
        <p v-if="sentence.subject && sentence.verb" class="translation">{{ sentence.es_translation }}</p>
        <p v-else class="text-lg font-medium text-gray-500">Selecciona sujeto y verbo.</p>
      </div>
      <div class="flex justify-center flex-wrap gap-2 mb-2">
        <p class="w-full text-center font-semibold text-gray-700">1. Elige un sujeto:</p>
        <div class="flex justify-center flex-wrap gap-2">
          <button v-for="subject in subjects" :key="subject" @click="selectSubject(subject)" :class="{ 'selected': sentence.subject === subject }" class="interactive-btn">{{ subject }}</button>
        </div>
      </div>
      <div class="flex justify-center flex-wrap gap-2">
        <p class="w-full text-center font-semibold text-gray-700 mt-4">2. Elige un verbo de rutina:</p>
        <div class="flex justify-center flex-wrap gap-2">
          <button v-for="(verb, key) in verbs" :key="key" @click="selectVerb(key)" :class="{ 'selected': sentence.verb === key }" class="interactive-btn">{{ key }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { gsap } from 'gsap';

export default {
  name: 'DailyLife',
  data() {
    return {
      subjects: ['I', 'You', 'He', 'She', 'We', 'They'],
      subjectEsMap: {
        'I': 'Yo',
        'You': 'Tú',
        'He': 'Él',
        'She': 'Ella',
        'We': 'Nosotros',
        'They': 'Ellos/Ellas',
      },
      verbs: {
        work: { base: 'work', s_form: 'works', es: 'trabajar' },
        study: { base: 'study', s_form: 'studies', es: 'estudiar' },
        eat: { base: 'eat', s_form: 'eats', es: 'comer' },
        live: { base: 'live', s_form: 'lives', es: 'vivir' },
        sleep: { base: 'sleep', s_form: 'sleeps', es: 'dormir' },
        drink: { base: 'drink', s_form: 'drinks', es: 'beber' },
      },
      sentence: {
        type: 'positive',
        subject: null,
        verb: null,
        finalVerb: '',
        aux: '',
        es_translation: '',
      },
    };
  },
  methods: {
    selectSubject(subject) {
      this.sentence.subject = subject;
    },
    selectVerb(verb) {
      this.sentence.verb = verb;
    },
    updateSentence() {
      const { type, subject, verb } = this.sentence;
      if (!subject || !verb) return;

      const verbInfo = this.verbs[verb];
      const es_subject = this.subjectEsMap[subject];
      let finalVerb = verbInfo.base;
      let aux = '';
      let es_translation = '';

      if (type === 'positive') {
        if (['He', 'She', 'It'].includes(subject)) {
          finalVerb = verbInfo.s_form;
        }
        es_translation = `(${es_subject} ${verbInfo.es}...)`;
      } else {
        if (['He', 'She', 'It'].includes(subject)) {
          aux = "doesn't";
        } else {
          aux = "don't";
        }
        es_translation = `(${es_subject} no ${verbInfo.es}...)`;
      }

      this.sentence.finalVerb = finalVerb;
      this.sentence.aux = aux;
      this.sentence.es_translation = es_translation;

      gsap.from(this.$refs.sentenceDisplay, { duration: 0.5, opacity: 0, y: -10 });
    },
  },
  watch: {
    'sentence.type': 'updateSentence',
    'sentence.subject': 'updateSentence',
    'sentence.verb': 'updateSentence',
  },
  mounted() {
    gsap.from(this.$el, { duration: 0.5, opacity: 0, y: 20 });
  },
};
</script>