const token = localStorage.getItem("token");
const username = localStorage.getItem("user") || "Usuario";

// inicializamos para modal de editar usuario
const modalElement = document.getElementById('modalEditarUsuario');
const bsModal = new bootstrap.Modal(modalElement);

const modalCrear = new bootstrap.Modal(document.getElementById('modalCrearUsuario'));
const modalPass = new bootstrap.Modal(document.getElementById('modalPassword'));

if (!token) window.location.href = `${BASE_URL}/auth/login`;

document.addEventListener("DOMContentLoaded", function() {
    const localName = localStorage.getItem('nombre_apellidos');
    const localUser = localStorage.getItem('user');

    const nameElement = document.getElementById('display-name');
    const userElement = document.getElementById('display-username');

    // Si existen en localStorage, los ponemos. Si no, ponemos un valor por defecto.
    if (localName) {
        nameElement.textContent = localName;
    }

    if (localUser) {
        userElement.textContent = "@" + localUser;
    }
});

document.querySelectorAll(".cerrar-sesion").forEach(boton => {
    boton.addEventListener("click", (event) => {
        event.preventDefault();
        alert("Cerrando sesión...");
        localStorage.removeItem("token");
        localStorage.removeItem("user");
        localStorage.removeItem("nombre_apellidos");
        window.location.href = `${BASE_URL}/auth/login`;
    });
});

// Seleccionamos el tbody que ya existe en el HTML
const tableBody = document.getElementById('table-body');

tableBody.addEventListener("click", (event) => {
    // Buscamos si el click (o el origen del click) fue en el botón de editar
    const boton = event.target.closest(".edit-silice");
    const botonDelete = event.target.closest(".delete-silice");

    if (botonDelete) {
        event.preventDefault();
        const userId = botonDelete.getAttribute("data-user-id");
        console.log("Eliminar usuario con ID:", userId);
        const confirmar = confirm("¿Estás seguro de que deseas eliminar el usuario con ID: " + userId + "?");

        if (confirmar) {
            // consumimos el api para eliminar el usuario
            fetch(`${BASE_URL}/api/users/delete/` + userId, {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": "Token " + token
                }
            })
            .then(response => response.json())
            .then(data => {
                
                if (data.error) {
                    alert("Error al eliminar usuario ID: " + userId + ". " + data.error);
                    return;
                }else {
                    alert("Usuario ID: " + userId + " eliminado.");
                }
                // recargamos la pagina para ver los cambios
                window.location.reload();
            })
            .catch(error => {
                alert("Error al eliminar usuario ID: " + userId);
                console.error("Error:", error);
            });

        }
    }
});

document.getElementById('table-body').addEventListener("click", async (event) => {
    const boton = event.target.closest(".edit-silice");
    const containerErrores = document.getElementById('container-errores-api-edit');
    const listaErrores = document.getElementById('lista-errores-edit');
    containerErrores.style.display = 'none';
    listaErrores.innerHTML = '';
    if (boton) {
        event.preventDefault();
        const userId = boton.getAttribute("data-user-id");
        
        // Llamamos a la API para obtener los datos actuales de este usuario
        await cargarDatosEnModal(userId);
    }
});

async function cargarDatosEnModal(id) {
    try {
        const response = await fetch(`${BASE_URL}/api/users/show/${id}`, {
            headers: { 'Authorization': `Token ${localStorage.getItem('token')}` }
        });
        const user = await response.json();
        console.log(user);

        // Llenamos los inputs con lo que nos devuelve el API
        document.getElementById('id').value = user.id;
        document.getElementById('nombreApellidos').value = user.nombre_apellidos;
        document.getElementById('correo').value = user.correo;
        document.getElementById('nif').value = !user.nif ? '' : user.nif;
        document.getElementById('tipo_id').value = user.tipo_id;

        // Mostramos el modal
        bsModal.show();
    } catch (error) {
        alert("Error al obtener los datos del usuario" + error);
    }
}

async function guardarCambios() {
    const id = document.getElementById('id').value;
    const containerErrores = document.getElementById('container-errores-api-edit');
    const listaErrores = document.getElementById('lista-errores-edit');
    containerErrores.style.display = 'none';
    listaErrores.innerHTML = '';

    
    const datos = {
        nombreApellidos: document.getElementById('nombreApellidos').value,
        correo: document.getElementById('correo').value,
        nif: document.getElementById('nif').value,
        tipo_id: document.getElementById('tipo_id').value
    };

    try {
        const response = await fetch(`${BASE_URL}/api/users/update/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Token ${localStorage.getItem('token')}`
            },
            body: JSON.stringify(datos)
        });

        if (response.ok) {
            alert("¡Usuario actualizado correctamente!");
            bsModal.hide();
            loadUsers(currentPage); // Recargamos la tabla para ver los cambios
        } else {
            const data = await response.json();

            if (data.errors) {
                containerErrores.style.display = 'block';
                
                Object.values(data.errors).forEach(mensaje => {
                    const li = document.createElement('li');
                    li.innerHTML = `<i class="bi bi-exclamation-circle me-2"></i>${mensaje}`;
                    listaErrores.appendChild(li);
                });

                Object.keys(data.errors).forEach(key => {
                    const input = document.getElementById(`create_${key}`);
                    if (input) {
                        input.classList.add('is-invalid');
                    }
                });
            } else {
                alert("Ocurrió un error inesperado.");
            }
        }

        
        
    } catch (error) {
        alert("Error al guardar");
    }
}

async function enviarNuevoUsuario() {
    const containerErrores = document.getElementById('container-errores-api');
    const listaErrores = document.getElementById('lista-errores');
    
    containerErrores.style.display = 'none';
    listaErrores.innerHTML = '';

    const payload = {
        user: document.getElementById('create_user').value,
        password: document.getElementById('create_password').value,
        repetir_password: '',
        nombreApellidos: document.getElementById('create_nombreApellidos').value,
        correo: document.getElementById('create_correo').value,
        nif: document.getElementById('create_nif').value,
        tipo_id: parseInt(document.getElementById('create_tipo_id').value)
    };

    try {
        const response = await fetch(`${BASE_URL}/api/users/create`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Token ${localStorage.getItem('token')}`
            },
            body: JSON.stringify(payload)
        });

        if (response.ok) {
            alert("Usuario creado con éxito");
            modalCrear.hide();
            document.getElementById('formCrearUsuario').reset();
            loadUsers(1);
        } else {
            // Manejo de errores del servidor
            const data = await response.json();
            
            if (data.errors) {
                containerErrores.style.display = 'block';
            
                Object.values(data.errors).forEach(mensaje => {
                    const li = document.createElement('li');
                    li.innerHTML = `<i class="bi bi-exclamation-circle me-2"></i>${mensaje}`;
                    listaErrores.appendChild(li);
                });

                Object.keys(data.errors).forEach(key => {
                    const input = document.getElementById(`create_${key}`);
                    if (input) {
                        input.classList.add('is-invalid');
                    }
                });
            } else {
                alert("Ocurrió un error inesperado.");
            }
        }
    } catch (error) {
        console.error("Error:", error);
    }
}

async function cargarTiposIdentificacion() {
    const select = document.getElementById('tipo_id');
    const API_TIPOS_URL = `${BASE_URL}/api/users/types`;

    try {
        const response = await fetch(API_TIPOS_URL, {
            headers: { 'Authorization': `Token ${localStorage.getItem('token')}` }
        });
        const tipos = await response.json();

        // 1. Limpiar el select
        select.innerHTML = '<option value="" selected disabled>Seleccione un tipo...</option>';

        // 2. Recorrer los datos e insertar las opciones
        // Asumiendo que el API devuelve: [{id: 1, nombre: 'DNI'}, ...]
        tipos.forEach(tipo => {
            const option = document.createElement('option');
            option.value = tipo.id;
            option.textContent = tipo.nombre;
            select.appendChild(option);
        });

    } catch (error) {
        console.error("Error al cargar tipos:", error);
        select.innerHTML = '<option value="">Error al cargar</option>';
    }
}

async function cargarTipos() {
    const select = document.getElementById('create_tipo_id');
    const API_TIPOS_URL = `${BASE_URL}/api/users/types`;

    try {
        const response = await fetch(API_TIPOS_URL, {
            headers: { 'Authorization': `Token ${localStorage.getItem('token')}` }
        });
        const tipos = await response.json();

        // 1. Limpiar el select
        select.innerHTML = '<option value="" selected disabled>Seleccione un tipo...</option>';

        // 2. Recorrer los datos e insertar las opciones
        // Asumiendo que el API devuelve: [{id: 1, nombre: 'DNI'}, ...]
        tipos.forEach(tipo => {
            const option = document.createElement('option');
            option.value = tipo.id;
            option.textContent = tipo.nombre;
            select.appendChild(option);
        });

    } catch (error) {
        console.error("Error al cargar tipos:", error);
        select.innerHTML = '<option value="">Error al cargar</option>';
    }
}

// Llamar a la función cuando cargue la página
document.addEventListener("DOMContentLoaded", cargarTiposIdentificacion);
document.addEventListener("DOMContentLoaded", cargarTipos);

function abrirModalPassword(userId) {
    document.getElementById('formPassword').reset();
    document.getElementById('pass_user_id').value = userId;
    document.getElementById('container-errores-api-password').style.display = 'none';
    modalPass.show();
}

async function ejecutarCambioPassword() {
    
    

    const userId = document.getElementById('pass_user_id').value;
    const adminPass = document.getElementById('admin_password_confirm').value;
    const newPass = document.getElementById('new_password').value;
    const newPassRep = document.getElementById('new_password_repeat').value;
    const errorDiv = document.getElementById('container-errores-api-password');
    const listaErrores = document.getElementById('lista-errores-password');
    errorDiv.style.display = 'none';
    listaErrores.innerHTML = '';

    // Validación local rápida
    if (newPass !== newPassRep) {
        document.getElementById('new_password_repeat').classList.add('is-invalid');
        return;
    }

    const payload = {
        user: username,
        admin_password: adminPass, // La clave de quien está logueado
        new_password: newPass,
        repetir_password: newPassRep
    };

    try {
        const response = await fetch(`${BASE_URL}/api/users/password/${userId}`, {
            method: 'PATCH', // O POST según tu API
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Token ${token}`
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (response.ok) {
            alert("Contraseña actualizada correctamente");
            modalPass.hide();
        } else {
            // Si el error es "Contraseña de admin incorrecta" u otros
            errorDiv.style.display = 'block';
            // ´pintamos los errores
            Object.values(data.errors).forEach(mensaje => {
                const li = document.createElement('li');
                li.innerHTML = `<i class="bi bi-exclamation-circle me-2"></i>${mensaje}`;
                listaErrores.appendChild(li);
            });
            //errorDiv.textContent = data.errors || "La contraseña de administrador es incorrecta.";
        }
    } catch (error) {
        console.error("Error:", error);
        alert("Error de comunicación con el servidor");
    }
}