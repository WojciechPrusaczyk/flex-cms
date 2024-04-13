const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or subdirectory deploy
    //.setManifestKeyPrefix('build/')

    .copyFiles([{
        from: './assets/icons',
        to: 'icons/[path][name].[ext]',
    }])

    /*
     * Dashboard
     */
    .addEntry('app', './assets/app.js')
    .addEntry('dashboard_login', './assets/react/containers/dashboard/login.jsx')
    .addEntry('dashboard_index', './assets/react/containers/dashboard/index.jsx')
    .addEntry('dashboard_main', './assets/react/containers/dashboard/main.jsx')
    .addEntry('dashboard_gallery', './assets/react/containers/dashboard/gallery/main.jsx')
    .addEntry('dashboard_settings', './assets/react/containers/dashboard/settings/main.jsx')
    .addEntry('dashboard_colors', './assets/react/containers/dashboard/colors/main.jsx')
    .addEntry('dashboard_stylesheets', './assets/react/containers/dashboard/stylesheets/main.jsx')
    .addEntry('dashboard_stylesheets_edit', './assets/react/containers/dashboard/stylesheets/edit.jsx')
    .addEntry('dashboard_scripts', './assets/react/containers/dashboard/scripts/main.jsx')
    .addEntry('dashboard_scripts_edit', './assets/react/containers/dashboard/scripts/edit.jsx')
    .addEntry('dashboard_sections', './assets/react/containers/dashboard/sections/main.jsx')
    .addEntry('dashboard_sections_edit', './assets/react/containers/dashboard/sections/edit.jsx')

    .addStyleEntry('dashboard_login_style', './assets/styles/dashboard/login.scss')
    .addStyleEntry('dashboard_index_style', './assets/styles/dashboard/index.scss')
    .addStyleEntry('dashboard_main_style', './assets/styles/dashboard/main.scss')
    .addStyleEntry('dashboard_gallery_style', './assets/styles/dashboard/gallery/main.scss')
    .addStyleEntry('dashboard_settings_style', './assets/styles/dashboard/settings/main.scss')
    .addStyleEntry('dashboard_colors_style', './assets/styles/dashboard/colors/main.scss')
    .addStyleEntry('dashboard_stylesheets_style', './assets/styles/dashboard/stylesheets/main.scss')
    .addStyleEntry('dashboard_stylesheets_edit_style', './assets/styles/dashboard/stylesheets/edit.scss')
    .addStyleEntry('dashboard_scripts_style', './assets/styles/dashboard/scripts/main.scss')
    .addStyleEntry('dashboard_scripts_edit_style', './assets/styles/dashboard/scripts/edit.scss')
    .addStyleEntry('dashboard_sections_style', './assets/styles/dashboard/sections/main.scss')
    .addStyleEntry('dashboard_sections_edit_style', './assets/styles/dashboard/sections/edit.scss')

    /*
     * Main page
     */
    .addEntry('index', './assets/react/containers/webpage/index/index.jsx')
    .addStyleEntry('index_style', './assets/styles/webpage/index/index.scss')

    /*
     * Gallery
     */
    .addEntry('gallery', './assets/react/containers/webpage/gallery/gallery.jsx')
    .addStyleEntry('gallery_style', './assets/styles/webpage/gallery/gallery.scss')

    /*
     * Contact form
     */
    .addEntry('form', './assets/react/containers/webpage/form/form.jsx')
    .addStyleEntry('form_style', './assets/styles/webpage/form/form.scss')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    .enableStimulusBridge('./assets/controllers.json')

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // configure Babel
    // .configureBabel((config) => {
    //     config.plugins.push('@babel/a-babel-plugin');
    // })

    // enables and configure @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    // .enableTypeScriptLoader()

    // uncomment if you use React
    .enableReactPreset()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
