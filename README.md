# Business-APIs

## Modelo de datos

![Modelo de datos Ventas](https://github.com/SczSca/Business-APIs/assets/90069772/cd5474c2-7c9a-4551-91ac-492839b8a69f)

### Tiendas
  Permite almacenar los datos de n tiendas, donde cada tienda tiene n clientes y n productos.
### Productos
  Permite almacenar los datos de n productos, donde cada producto está asignado a una sola tienda.
   #### Views relacionados
  * [vw_detTickets_productos]
  * [vw_detTickets_productos_temp]
  * [vw_reporte_ventas_productos]
###  Clientes
  Permite almacenar los datos de n clientes, donde cada cliente está asignado especificamente a una tienda.
  #### Views relacionados
  * [vw_ticket_cliente]
  * [vw_abonos_ticket]
### Tickets
  Permite almacenar los datos de Tickets generados por un cliente, donde cada cliente puede tener n tickets, cada ticket puede tener varios registros de detalles_ticket y cada ticket puede tener n abonos ( la cantidad dependerá de la lógica de negocios).
  #### Views relacionados
  * [vw_ticket_cliente]
  * [vw_abonos_ticket]
  * [vw_detTickets_productos]
  #### Triggers relacionados
  * [Update_ticketEstado]
### Detalles_ticket
  Permite almacenar datos de los productos facturados de un ticket, donde cada producto esta relacionado a un ticket en especifico.
  #### Views relacionados
  * [vw_detTickets_productos]
#### Triggers relacionados
  * [Delete_ticketTotal]
  * [Insert_detalleTicketPrecio]
  * [Update_detalleTicketPrecio]
  * [Update_ticketTotal]
  * [Insert_ticketTotal]
### Tickets_temp
  Permite almacenar los datos de los productos del carrito de compras, donde cada cliente puede tener un solo ticket/carrito de compras y cada ticket temporal/carrito de compras puede tener varios registros de detalles_ticket_temp.
  #### Views relacionados
  * [vw_detTickets_productos_temp]
### Detalles_ticket_temp
  Permite almacenar datos de los productos guardados en un ticket temporal/carrito de compras, donde cada producto esta relacionado a un ticket temporal/carrito de compras.
  #### Views relacionados
  * [vw_detTickets_productos_temp]
  #### Triggers relacionados
  * [Delete_productoCantidadCarrito]
  * [Delete_ticketTotalTemp]
  * [Insert_detalleTicketPrecioTemp]
  * [Insert_productoCantidadCarrito]
  * [Update_detalleTicketPrecioTemp]
  * [Update_productoCantidadCarrito]
  * [Update_ticketTotalTemp]
  * [Insert_ticketTotalTemp]
  
### Abonos
  Permite almacenar los datos sobre el pago/abono de un ticket
  #### Views relacionados
  * [vw_abonos_ticket]
  #### Triggers relacionados
  * [Update_ticketCredito]

### Procedimientos Almacenados
  * [ AbonosOtrosPlazos( f_inicioParam, condicionales, fk_tiendaParam ) ]
    ( Lectura ) encargado de obtener y retornar todos los otros abonos realizados en otro tiempo que no se encuentra dentro del plazo especificado. Utilizado en la api de reportes
  * [ ReporteVentasProductos( f_inicioParam, f_finalParam, id_tiendaParam ) ]
    ( Lectura ) encargado de obtener los datos de los productos facturados en los tickets del plazo especificado. Utilizado en la api de reportes
  * [ TicketsConjuntoDeIds( condicional, id_tiendaParam) ]
    ( Lectura ) encargado de obtener los datos de los tickets que estan relacionados con los abonos del plazo especificado. Utilizado en la api de reportes.
  * [ TraspasarCarrito( id_clienteParam, id_ticketParam, id_tiendaParam) ]
    Pasa los datos de ticket_temp y detalles_ticket_temp a un nuevo ticket y detalles_ticket_temp y elimina los registros en las tablas temporales/carrito de compras. Utilizado en la api de tickets_temp.
