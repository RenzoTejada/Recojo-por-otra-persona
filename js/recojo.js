var chck = jQuery("#billing_recojo").is(":checked");

if (chck) {
    document.getElementById("billing_recojo_nombre_field").style.display = "block";
    document.getElementById("billing_recojo_apellido_field").style.display = "block";
    document.getElementById("billing_recojo_dni_field").style.display = "block";
} else {
    document.getElementById("billing_recojo_nombre_field").style.display = "none";
    document.getElementById("billing_recojo_apellido_field").style.display = "none";
    document.getElementById("billing_recojo_dni_field").style.display = "none";
}

jQuery('#billing_recojo').on('click', function () {
    var chck = jQuery("#billing_recojo").is(":checked");

    if (chck) {
        document.getElementById("billing_recojo_nombre_field").style.display = "block";
        document.getElementById("billing_recojo_apellido_field").style.display = "block";
        document.getElementById("billing_recojo_dni_field").style.display = "block";
    } else {
        document.getElementById("billing_recojo_nombre_field").style.display = "none";
        document.getElementById("billing_recojo_apellido_field").style.display = "none";
        document.getElementById("billing_recojo_dni_field").style.display = "none";
    }
});

jQuery('#billing_recojo_dni').blur(function (event) {
    var dni = jQuery('#billing_recojo_dni').val();
    rt_recojo_validar_dni(dni);

});

function rt_recojo_validar_dni(dni) {
    if (isNaN(dni)) {
        jQuery('#billing_recojo_dni').val('');
        alert('El dni no debe tener letras');
    } else if (dni.length > 8) {
        jQuery('#billing_recojo_dni').val('');
        alert('El dni ingresado es incorrecto');
    }
}