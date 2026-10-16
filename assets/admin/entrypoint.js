import { Application } from '@hotwired/stimulus';

import './styles/builder.scss';
import BuilderController from './controllers/builder_controller.js';

// A dedicated Stimulus application: the plugin controllers are registered here so they do not
// depend on the host application's controllers.json.
const application = Application.start();
application.register('akawaka-cms-builder', BuilderController);

window.AkawakaCmsStimulus = application;
