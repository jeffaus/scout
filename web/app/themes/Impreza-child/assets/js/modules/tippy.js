/**
 * Complete session
 */
import { delegate } from 'tippy.js';
import 'tippy.js/dist/tippy.css'; // optional for styling
import 'tippy.js/themes/light.css';

export default () => {

  delegate('body', {
    target: '[data-next-disabled]',
    theme: 'light',
    content: "Come back tomorrow to unlock the next day!",
  });

};


