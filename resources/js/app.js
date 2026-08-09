import Alpine from 'alpinejs';
import '../css/app.css';
import { translationEditor } from './translation-editor.js';

window.Alpine = Alpine;

Alpine.data('translationEditor', translationEditor);
Alpine.start();
