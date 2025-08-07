<template>
  <div>
    <h2 class="text-3xl font-bold text-center mb-6">En el Restaurante: Pedir Comida</h2>
    <p class="text-center text-lg mb-8">Aprende a usar cuantificadores y frases clave para pedir comida y expresar lo que te gusta.</p>
    <div class="card mb-8">
      <h3 class="text-xl font-bold mb-4 text-center">Práctica de Cuantificadores</h3>
      <div class="mt-4">
        <div class="mb-2">
          <p class="text-lg font-semibold text-center">{{ currentQuiz.q }}</p>
          <p class="pronunciation text-center">{{ currentQuiz.p }}</p>
        </div>
        <div class="flex justify-center flex-wrap gap-4 mb-2">
          <button v-for="option in currentQuiz.o" :key="option" @click="checkAnswer(option)" class="interactive-btn">{{ option }}</button>
        </div>
        <p class="feedback text-center" :class="feedbackClass">{{ feedback }}</p>
        <div class="text-center mt-4" v-if="showNextButton">
          <button @click="nextQuestion" class="interactive-btn">Siguiente</button>
        </div>
      </div>
    </div>
    <div class="grid md:grid-cols-2 gap-8 mb-8">
      <div class="card">
        <h3 class="text-xl font-bold mb-4 text-center">Pedir Comida y Bebidas</h3>
        <div class="space-y-3">
          <div>
            <p><strong class="text-blue-700">Can I have...?</strong> (¿Puedo tener...?)</p>
            <p class="pl-4 italic">"Can I have a coffee, please?"</p>
            <p class="pl-4 pronunciation">/kan ai hav a kó-fi pliz/</p>
          </div>
          <div>
            <p><strong class="text-blue-700">I'd like...</strong> (Me gustaría...)</p>
            <p class="pl-4 italic">"I'd like the chicken sandwich."</p>
            <p class="pl-4 pronunciation">/aid laik da chi-ken sánd-wich/</p>
          </div>
        </div>
      </div>
      <div class="card">
        <h3 class="text-xl font-bold mb-4 text-center">Expresar Preferencias</h3>
        <div class="space-y-3">
          <div>
            <p><strong class="text-green-700">I like...</strong> (Me gusta/n...)</p>
            <p class="pl-4 italic">"I like pizza."</p>
            <p class="pl-4 pronunciation">/ai laik pit-sa/</p>
          </div>
          <div>
            <p><strong class="text-red-700">I don't like...</strong> (No me gusta/n...)</p>
            <p class="pl-4 italic">"I don't like spicy food."</p>
            <p class="pl-4 pronunciation">/ai dóunt laik spái-si fud/</p>
          </div>
        </div>
      </div>
    </div>
    <div>
      <h3 class="text-2xl font-bold text-center mb-4">Vocabulario de Comida (¡Haz clic!)</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="food in foodVocab" :key="food.en" @click="flipCard($event)" class="flip-card interactive-card h-24">
          <div class="flip-card-inner">
            <div class="flip-card-front"><p class="font-bold text-lg">{{ food.en }}</p><p class="pronunciation">{{ food.p }}</p></div>
            <div class="flip-card-back"><p class="font-bold text-lg">{{ food.es }}</p></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { gsap } from 'gsap';

export default {
  name: 'Restaurant',
  data() {
    return {
      quantifierQuizzes: [
        { q: "I have ___ apples.", p: "/ai hav ___ á-pls/", o: ["some", "any"], a: "some", c: "Usa 'some' para oraciones positivas." },
        { q: "I don't have ___ bananas.", p: "/ai dóunt hav ___ ba-ná-nas/", o: ["some", "any"], a: "any", c: "Usa 'any' para oraciones negativas." },
        { q: "Do you have ___ questions?", p: "/du yu hav ___ kwés-tions/", o: ["some", "any"], a: "any", c: "Usa 'any' para preguntas." },
        { q: "How ___ apples do you want?", p: "/jáu ___ á-pls du yu wont/", o: ["much", "many"], a: "many", c: "'apples' es contable." },
        { q: "How ___ water do you drink?", p: "/jáu ___ wó-ter du yu drink/", o: ["much", "many"], a: "much", c: "'water' es incontable." },
        { q: "I have ___ of friends.", p: "/ai hav ___ ov frends/", o: ["a lot", "a few"], a: "a lot", c: "'a lot of' significa muchos/as." },
        { q: "I need just ___ books.", p: "/ai nid yast ___ buks/", o: ["a little", "a few"], a: "a few", c: "'books' es contable." },
        { q: "Add ___ sugar, please.", p: "/ad ___ shú-gar pliz/", o: ["a little", "a few"], a: "a little", c: "'sugar' es incontable." }
      ],
      foodVocab: [
        { en: 'Bread', es: 'Pan', p: '/bréd/' }, { en: 'Cheese', es: 'Queso', p: '/chiz/' },
        { en: 'Chicken', es: 'Pollo', p: '/chi-ken/' }, { en: 'Water', es: 'Agua', p: '/wo-ter/' },
        { en: 'Meat', es: 'Carne', p: '/mit/' }, { en: 'Fish', es: 'Pescado', p: '/fish/' },
        { en: 'Rice', es: 'Arroz', p: '/rais/' }, { en: 'Apple', es: 'Manzana', p: '/a-pl/' }
      ],
      currentQuizIndex: 0,
      feedback: '',
      feedbackClass: '',
      showNextButton: false,
      correctAnswers: 0,
    };
  },
  computed: {
    currentQuiz() {
      return this.quantifierQuizzes[this.currentQuizIndex];
    },
  },
  methods: {
    checkAnswer(selected) {
      if (selected === this.currentQuiz.a) {
        this.feedback = `¡Correcto! ${this.currentQuiz.c}`;
        this.feedbackClass = 'correct';
        this.showNextButton = true;
        this.correctAnswers++;
      } else {
        this.feedback = 'Inténtalo de nuevo.';
        this.feedbackClass = 'incorrect';
      }
    },
    nextQuestion() {
      if (this.currentQuizIndex < this.quantifierQuizzes.length - 1) {
        this.currentQuizIndex++;
        this.feedback = '';
        this.feedbackClass = '';
        this.showNextButton = false;
      } else {
        // All questions answered, emit quiz-completed event
        this.$emit('quiz-completed', this.correctAnswers, {
          section: 'restaurant',
          totalQuestions: this.quantifierQuizzes.length,
          correctAnswers: this.correctAnswers,
        });
      }
    },
    flipCard(event) {
      const card = event.currentTarget;
      gsap.to(card.querySelector('.flip-card-inner'), {
        rotationY: "+=180",
        duration: 0.6,
        ease: 'power2.inOut'
      });
    },
  },
  mounted() {
    gsap.from(this.$el, { duration: 0.5, opacity: 0, y: 20 });
  },
};
</script>

<style scoped>
.flip-card {
  perspective: 1000px;
}
.flip-card-inner {
  position: relative;
  width: 100%;
  height: 100%;
  text-align: center;
  transition: transform 0.6s;
  transform-style: preserve-3d;
}
.flip-card-front, .flip-card-back {
  position: absolute;
  width: 100%;
  height: 100%;
  -webkit-backface-visibility: hidden;
  backface-visibility: hidden;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 1rem;
  border-radius: 12px;
}
.flip-card-front {
  background-color: white;
  border: 1px solid #e0e0e0;
}
.flip-card-back {
  background-color: #4A55A2;
  color: white;
  transform: rotateY(180deg);
}
</style>
