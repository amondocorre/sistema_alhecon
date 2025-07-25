<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
$route['api/login'] = 'UserController/login';
$route['api/logout'] = 'UserController/logout';
$route['api/getMenuAccess'] = 'UserController/getMenuAccess';
$route['api/user/create_user'] = 'UserController/create_user';
$route['api/user/update_user/(:num)'] = 'UserController/update_user/$1';
$route['api/user/delete/(:num)'] = 'UserController/delete/$1';
$route['api/user/activate/(:num)'] = 'UserController/activate/$1';
$route['api/user/getAllUsers'] = 'UserController/getAllUsers';
$route['api/user/findActive'] = 'UserController/findActive';
$route['api/user/setStateUser/(:num)'] = 'UserController/setStateUser/$1';
$route['api/user/getButtonsAccesUser/(:num)'] = 'UserController/getButtonsAccesUser/$1';
//perfiles
$route['api/perfil/getPerfil'] = 'PerfilController/getPerfil';
$route['api/perfil/findAllPerfil'] = 'PerfilController/findAllPerfil';
$route['api/perfil/create'] = 'PerfilController/create';
$route['api/perfil/update/(:any)'] = 'PerfilController/update/$1';
$route['api/perfil/delete/(:any)'] = 'PerfilController/delete/$1';
$route['api/perfil/activate/(:any)'] = 'PerfilController/activate/$1';

//client
$route['api/client/findActive'] = 'ClientController/findActive';
$route['api/client/findAll'] = 'ClientController/findAll';
$route['api/client/create'] = 'ClientController/create';
$route['api/client/update/(:any)'] = 'ClientController/update/$1';
$route['api/client/delete/(:any)'] = 'ClientController/delete/$1';
$route['api/client/activate/(:any)'] = 'ClientController/activate/$1';

//access
$route['api/config/access/getPerfil'] = 'configurations/MenuAccessController/getClient';
$route['api/config/access/findAll'] = 'configurations/MenuAccessController/findAll';
$route['api/config/access/create'] = 'configurations/MenuAccessController/create';
$route['api/config/access/update/(:any)'] = 'configurations/MenuAccessController/update/$1';
$route['api/config/access/delete/(:any)'] = 'configurations/MenuAccessController/delete/$1';
$route['api/config/access/activate/(:any)'] = 'configurations/MenuAccessController/activate/$1';

//Button
$route['api/config/button/findActive'] = 'configurations/ButtonController/findActive';
$route['api/config/button/findAll'] = 'configurations/ButtonController/findAll';
$route['api/config/button/create'] = 'configurations/ButtonController/create';
$route['api/config/button/update/(:any)'] = 'configurations/ButtonController/update/$1';
$route['api/config/button/delete/(:any)'] = 'configurations/ButtonController/delete/$1';

// Security
$route['api/security/acces/findByUser/(:any)'] = 'security/AccesUserController/findByUser/$1';
$route['api/security/acces/update/(:any)/(:any)'] = 'security/AccesUserController/update/$1/$2';

$route['api/security/acces-perfil/findByPerfil/(:any)'] = 'security/AccesPerfilController/findByPerfil/$1';
$route['api/security/acces-perfil/update/(:any)/(:any)'] = 'security/AccesPerfilController/update/$1/$2';

// Mascotas
$route['api/config/status/findActive'] = 'configurations/StatusController/findActive';
$route['api/config/status/findAll'] = 'configurations/StatusController/findAll';
$route['api/config/status/create'] = 'configurations/StatusController/create';
$route['api/config/status/update/(:any)'] = 'configurations/StatusController/update/$1';
$route['api/config/status/delete/(:any)'] = 'configurations/StatusController/delete/$1';
$route['api/config/status/activate/(:any)'] = 'configurations/StatusController/activate/$1';
// productos
$route['api/config/product/findActive'] = 'configurations/ProductController/findActive';
$route['api/config/product/findAll'] = 'configurations/ProductController/findAll';
$route['api/config/product/create'] = 'configurations/ProductController/create';
$route['api/config/product/update/(:any)'] = 'configurations/ProductController/update/$1';
$route['api/config/product/delete/(:any)'] = 'configurations/ProductController/delete/$1';
$route['api/config/product/activate/(:any)'] = 'configurations/ProductController/activate/$1';

//Combos
$route['api/config/combo/findActive'] = 'configurations/ComboController/findActive';
$route['api/config/combo/findAll'] = 'configurations/ComboController/findAll';
$route['api/config/combo/create'] = 'configurations/ComboController/create';
$route['api/config/combo/update/(:any)'] = 'configurations/ComboController/update/$1';
$route['api/config/combo/delete/(:any)'] = 'configurations/ComboController/delete/$1';
$route['api/config/combo/activate/(:any)'] = 'configurations/ComboController/activate/$1';
// metodo pago
$route['api/config/payment-method/findActive'] = 'configurations/PaymentMethodController/findActive';
$route['api/config/payment-method/findAll'] = 'configurations/PaymentMethodController/findAll';
$route['api/config/payment-method/create'] = 'configurations/PaymentMethodController/create';
$route['api/config/payment-method/update/(:any)'] = 'configurations/PaymentMethodController/update/$1';
$route['api/config/payment-method/delete/(:any)'] = 'configurations/PaymentMethodController/delete/$1';
$route['api/config/payment-method/activate/(:any)'] = 'configurations/PaymentMethodController/activate/$1';
// sucursal
$route['api/config/sucursal/findActive'] = 'configurations/SucursalController/findActive';
$route['api/config/sucursal/findAll'] = 'configurations/SucursalController/findAll';
$route['api/config/sucursal/create'] = 'configurations/SucursalController/create';
$route['api/config/sucursal/update/(:any)'] = 'configurations/SucursalController/update/$1';
$route['api/config/sucursal/delete/(:any)'] = 'configurations/SucursalController/delete/$1';
$route['api/config/sucursal/activate/(:any)'] = 'configurations/SucursalController/activate/$1';
// sucursal usuario
$route['api/config/sucursal-user/getSucursalesUser'] = 'configurations/SucursalUsuarioController/getSucursalesUser';
$route['api/config/sucursal-user/findAll'] = 'configurations/SucursalUsuarioController/findAll';
$route['api/config/sucursal-user/addSucursales'] = 'configurations/SucursalUsuarioController/addSucursales';
// proveedor
$route['api/config/supplier/findActive'] = 'configurations/SupplierController/findActive';
$route['api/config/supplier/findAll'] = 'configurations/SupplierController/findAll';
$route['api/config/supplier/create'] = 'configurations/SupplierController/create';
$route['api/config/supplier/update/(:any)'] = 'configurations/SupplierController/update/$1';
$route['api/config/supplier/delete/(:any)'] = 'configurations/SupplierController/delete/$1';
$route['api/config/supplier/activate/(:any)'] = 'configurations/SupplierController/activate/$1';
$route['api/config/calendar/obtenerFeriados'] = 'configurations/CalendarController/obtenerFeriados';
$route['api/config/calendar/poblarCalendarioPorMes'] = 'configurations/CalendarController/poblarCalendarioPorMes';
$route['api/config/calendar/poblarCalendarioPorAño'] = 'configurations/CalendarController/poblarCalendarioPorAño';
// caja
$route['api/caja/findActive/(:any)'] = 'caja/CajaController/findActive/$1';
$route['api/caja/findAll'] = 'caja/CajaController/findAll';
$route['api/caja/create/(:any)'] = 'caja/CajaController/create/$1';
$route['api/caja/update/(:any)/(:any)'] = 'caja/CajaController/update/$1/$2';
$route['api/caja/delete/(:any)'] = 'caja/CajaController/delete/$1';
$route['api/caja/activate/(:any)'] = 'caja/CajaController/activate/$1';
// caja
$route['api/caja-movi/findFilter'] = 'caja/BoxMovementController/findFilter';
$route['api/caja-movi/findAll'] = 'caja/BoxMovementController/findAll';
$route['api/caja-movi/create/(:any)'] = 'caja/BoxMovementController/create/$1';
$route['api/caja-movi/update/(:any)'] = 'caja/BoxMovementController/update/$1';
$route['api/caja-movi/delete/(:any)'] = 'caja/BoxMovementController/delete/$1';
$route['api/caja-movi/activate/(:any)'] = 'caja/BoxMovementController/activate/$1';
// alquileres
$route['api/rent/getDataRequerid'] = 'RentController/getDataRequerid';
$route['api/rent/registerRent'] = 'RentController/registerRent';
$route['api/rent/registerReturn'] = 'RentController/registerReturn';
$route['api/rent/listRentals'] = 'RentController/listRentals';
$route['api/rent/getDataReturn/(:any)'] = 'RentController/getAlquilerById/$1';
//inventarios
$route['api/inventario/getStock/(:any)'] = 'InventoryController/getStock/$1';
//compras 
$route['api/compra/register'] = 'CompraController/register';
$route['api/compra/update'] = 'CompraController/register';
$route['api/compra/list'] = 'CompraController/list';


$route['api/config/company/findActive'] = 'configurations/CompanyController/findActive';
$route['api/config/company/findAll'] = 'configurations/CompanyController/findAll';
$route['api/config/company/create'] = 'configurations/CompanyController/create';
$route['api/config/company/update/(:any)'] = 'configurations/CompanyController/update/$1';
$route['api/config/company/delete/(:any)'] = 'configurations/CompanyController/delete/$1';

//Impresion
$route['api/impresion/imprimirMovimientoCaja/(:any)'] = 'Impresion/imprimirMovimientoCaja/$1';
$route['api/impresion/imprimirAperturaTurno/(:any)'] = 'Impresion/imprimirAperturaTurno/$1';
$route['api/impresion/imprimirCierreTurno/(:any)'] = 'Impresion/imprimirCierreTurno/$1';
$route['api/impresion/imprimirContrato/(:any)'] = 'Impresion/imprimirContrato/$1';
$route['api/impresion/imprimirReciboPago/(:any)'] = 'Impresion/imprimirReciboPago/$1';
$route['api/config/company/activate/(:any)'] = 'configurations/CompanyController/activate/$1';

$route['api/client-company/findActive'] = 'ClientCompanyController/findActive';
$route['api/client-company/findAll'] = 'ClientCompanyController/findAll';
$route['api/client-company/create'] = 'ClientCompanyController/create';
$route['api/client-company/update/(:any)'] = 'ClientCompanyController/update/$1';
$route['api/client-company/delete/(:any)'] = 'ClientCompanyController/delete/$1';
$route['api/client-company/activate/(:any)'] = 'ClientCompanyController/activate/$1';
// reportes 
$route['api/report/reportCierreTurno'] = 'reports/ReportController/reportCierreTurno';
$route['api/report/reportContratos'] = 'reports/ReportController/reportContratos';
$route['api/report/reportContratoDeudas'] = 'reports/ReportController/reportContratoDeudas';

//dashboard
$route['api/dashboard/arrivals-departures'] = 'dashboard/DashboardController/getArrivalsDepartures';
$route['api/dashboard/occupation'] = 'dashboard/DashboardController/getOccupation';
$route['api/dashboard/total_clientes'] = 'dashboard/DashboardController/getTotalClientes';
$route['api/dashboard/total_mascotas_estancia'] = 'dashboard/DashboardController/getMascotasEstancia';
$route['api/dashboard/total_ingresos_diarios'] = 'dashboard/DashboardController/getIngresosDiarios';
