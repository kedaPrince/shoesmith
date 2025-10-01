# Bash Scripts

Run `cd bash` to get in the bash folder

## Front-end page generator

Run `bash create_frontend_page.sh "page name"` to create a frontend page's view and controller.

E.g `bash create_frontend_page.sh "about us"`

## Back-end module generator

### Default 

The default Back-end module generator will generate the view, controller, model and it will create the database table with the default table structure. it will also add the general_labels for the module in to the general_lang.php file

Run `bash create_backend_module.sh "module-name in plural" "module-name in singular" "module group"`  to create a backend module.

E.g `bash create_backend_module.sh "products" "product" "shop"`
