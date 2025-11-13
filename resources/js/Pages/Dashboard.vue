<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

// Estado de la lista de tickets: Ahora maneja el objeto de paginación
const tickets = ref({ data: [], links: [], total: 0 });

// Estado de la notificación (Reemplazo para alert())
const notification = ref({
    show: false,
    message: '',
    type: 'success', // 'success' or 'error'
    timeout: null,
});

// 1. Asegúrate de que los usuarios se reciban como una Prop.
const props = defineProps({
    tickets: Object,
    users: Array, // 
    // ...
});

const users = computed(() => props.users);
// Opciones de filtro de estado
const statusOptions = [
    { value: '', label: 'Todos' },
    { value: 'open', label: 'Abierto' },
    { value: 'in_progress', label: 'En Progreso' },
    { value: 'closed', label: 'Cerrado' },
];
const perPageOptions = [5, 10, 20, 50]; // Opciones de paginación

// Estado local para los filtros, paginación y ordenación
const currentFilters = ref({
    search: '',
    status: '',
    per_page: 10, 
    sort_column: 'created_at',
    sort_direction: 'desc',
    page: 1, 
});

// Columna de seguridad: Define las columnas que pueden ser ordenadas
const sortableColumns = [
    { key: 'id', label: 'ID' },
    { key: 'title', label: 'Detalle' },
    { key: 'status', label: 'Estado' },
    // created_at es la columna por defecto y se ordenará desde el backend
];

// Estado y lógica para el debounce de la búsqueda (DECLARACIÓN ÚNICA)
const searchTimeout = ref(null);

const form = useForm({
    title: '',
    description: '',
    user_id: props.users.length > 0 ? props.users[0].id : 1, // Inicializa con el primer usuario si existe
});

// ----------------------------------------------------------------------
// LÓGICA DE NOTIFICACIONES
// ----------------------------------------------------------------------

const showNotification = (message, type = 'success') => {
    if (notification.value.timeout) {
        clearTimeout(notification.value.timeout);
    }

    notification.value.message = message;
    notification.value.type = type;
    notification.value.show = true;

    notification.value.timeout = setTimeout(() => {
        notification.value.show = false;
    }, 4000);
};

// ----------------------------------------------------------------------
// LÓGICA DE FILTRADO, PAGINACIÓN Y ORDENACIÓN
// ----------------------------------------------------------------------

// Función principal de carga de tickets (CORREGIDA)
const fetchTickets = async () => {
    // 1. Construir la URL con todos los parámetros
    // Filtra las propiedades vacías antes de construir la cadena de consulta
    const params = new URLSearchParams(
        Object.fromEntries(
            Object.entries(currentFilters.value).filter(([, v]) => v !== '' && v !== null)
        )
    );
    const queryString = params.toString();

    // 2. LLamada al endpoint API con filtros y paginación
    try {
        const response = await fetch(`/api/tickets?${queryString}`);
        const data = await response.json();
        
        // 3. ASIGNACIÓN CLAVE: Asigna el objeto de paginación completo
        tickets.value = data; 
    } catch (error) {
        console.error("Error al cargar tickets:", error);
        showNotification('Error al cargar la lista de tickets.', 'error');
    }
};

// Lógica de Debounce
const runSearch = () => {
    clearTimeout(searchTimeout.value);
    searchTimeout.value = setTimeout(() => {
        currentFilters.value.page = 1; // Siempre vuelve a la página 1 al buscar
        fetchTickets();
    }, 300); // 300ms de retraso
};

// Lógica de Ordenación
const setSort = (column) => {
    if (currentFilters.value.sort_column === column) {
        currentFilters.value.sort_direction = currentFilters.value.sort_direction === 'asc' ? 'desc' : 'asc';
    } else {
        currentFilters.value.sort_column = column;
        currentFilters.value.sort_direction = 'desc';
    }
    currentFilters.value.page = 1; 
    fetchTickets();
};

// Función para borrar la ordenación de columna de manera individual
const clearSort = () => {
    currentFilters.value.sort_column = 'created_at';
    currentFilters.value.sort_direction = 'desc';
    // El watcher detectará el cambio y llamará a fetchTickets.
};

// Función para cambiar de página
const changePage = (url) => {
    if (!url) return;
    
    // Extrae la página del link de paginación
    const urlObj = new URL(url);
    currentFilters.value.page = urlObj.searchParams.get('page');
    // Note: fetchTickets() ya es llamado por el Watcher 3.
};

// Función para restablecer todos los filtros
const resetFilters = () => {
    currentFilters.value.search = '';
    currentFilters.value.status = '';
    currentFilters.value.sort_column = 'created_at'; // Vuelve a la columna por defecto
    currentFilters.value.sort_direction = 'desc'; // Vuelve a la dirección por defecto
    currentFilters.value.page = 1;
    // La función watch se encargará de llamar a fetchTickets()
};

// Función para borrar el campo de búsqueda
const clearSearch = () => {
    currentFilters.value.search = '';
    // El watch detectará el cambio y llamará a runSearch() para recargar
};

// ----------------------------------------------------------------------
// WATCHERS PARA EJECUTAR LA CARGA DE DATOS
// ----------------------------------------------------------------------

// Watcher 1: Ejecuta la búsqueda con debounce.
// Nota: currentFilters.value.search es la cadena.
watch(() => currentFilters.value.search, (newSearch, oldSearch) => {
    if (newSearch !== oldSearch) {
        runSearch();
    }
});

// Watcher 2: Ejecuta fetchTickets inmediatamente para Status y Per Page. (Puntos 3, 4, 5)
// El uso de un array forzará la detección de cambios en ambas propiedades.
watch([() => currentFilters.value.status, () => currentFilters.value.per_page], () => {
    currentFilters.value.page = 1; // Siempre vuelve a la página 1
    fetchTickets();
});

// Watcher 3: Ejecuta fetchTickets solo para cambios de página y ordenación. (Punto 5)
// Nota: La paginación real solo cambia 'page', 'sort_column' y 'sort_direction'
watch([() => currentFilters.value.page, () => currentFilters.value.sort_column, () => currentFilters.value.sort_direction], () => {
    fetchTickets();
}, { deep: false }); // No deep, ya que estamos observando propiedades específicas

// ----------------------------------------------------------------------
// LÓGICA DE CREACIÓN Y ACTUALIZACIÓN
// ----------------------------------------------------------------------

// Creación de Ticket
const submitTicket = async () => {
    try {
        const response = await fetch('/api/tickets', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                // Asegúrate de que el token CSRF esté disponible en el <head> de tu HTML
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : ''
            },
            body: JSON.stringify(form.data()),
        });
        
        const data = await response.json();
        
        if (response.ok) {
            showNotification('Ticket creado con éxito!');
            form.reset(); 
            fetchTickets(); // Recarga la lista
        } else {
            console.error('Error al crear ticket:', data);
            showNotification('Error al crear ticket: ' + (data.message || 'Verifica la consola.'), 'error');
        }

    } catch (e) {
        console.error('Error de red:', e);
        showNotification('Error de red al crear ticket.', 'error');
    }
};

// Actualización de Estado
const updateStatus = async (ticket) => {
    // LLamada al endpoint PUT /api/tickets/{id}/status
    try {
        const response = await fetch(`/api/tickets/${ticket.id}/status`, { method: 'PUT' });
        if (response.ok) {
             fetchTickets(); // Recargar la lista para ver el nuevo estado
             showNotification('Estado del ticket actualizado con éxito!');
        } else {
            const errorData = await response.json();
            console.error('Error al actualizar estado:', errorData);
            showNotification('Error al actualizar estado. Consulta la consola.', 'error');
        }
    } catch (e) {
        console.error('Error de red al actualizar estado:', e);
        showNotification('Error de red al actualizar estado.', 'error');
    }
};


// ----------------------------------------------------------------------
// INICIALIZACIÓN
// ----------------------------------------------------------------------
onMounted(() => {
    // La paginación inicial debe cargarse AQUI.
    fetchTickets();
});
</script>

<template>
    <!-- Componente de Notificación (Reemplaza alert()) -->
    <div v-if="notification.show" :class="[
        'fixed top-0 left-1/2 transform -translate-x-1/2 mt-4 z-50 p-4 rounded-lg shadow-xl text-white transition-all duration-300 max-w-sm w-11/12 text-center',
        notification.type === 'success' ? 'bg-green-600' : 'bg-red-600'
    ]">
        <div class="flex items-center justify-center">
            <span class="mr-2 text-lg">
                {{ notification.type === 'success' ? '✓' : '✗' }}
            </span>
            <span>{{ notification.message }}</span>
        </div>
    </div>


    <div class="p-4 sm:p-8 max-w-7xl mx-auto w-11/12 sm:w-full">

        <!-- Formulario de Creación de Ticket -->
        <!-- Contenedor Flex para dividir en dos columnas en escritorio -->
        <div class="mb-6 bg-white shadow-xl rounded-lg border-t-4 border-indigo-500 flex flex-col md:flex-row">

            <!-- Columna 1: Título Estilizado -->
            <!-- Ocupa la mitad del espacio en md: y superiores, y el 100% en móvil (apilado) -->
            <div class="bg-indigo-600 p-6 md:p-8 rounded-t-lg md:rounded-l-lg md:rounded-tr-none flex items-center justify-center text-center flex-1 order-1 md:order-none">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white leading-tight">
                    Panel de Gestión de Tickets
                </h2>
            </div>
            
            <!-- Columna 2: Formulario -->
            <!-- Ocupa la mitad del espacio en md: y superiores, y el 100% en móvil -->
            <div class="p-4 sm:p-6 flex-1 order-2 md:order-none">
                
                <h3 class="text-xl font-bold text-gray-800 mb-4 md:hidden">Crear Nuevo Ticket</h3>
                
                <form @submit.prevent="submitTicket" class="space-y-3">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

                        <div class="md:col-span-2">
                            <label for="title" class="block text-sm font-medium text-gray-700">Título</label>
                            <input v-model="form.title" type="text" id="title" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700">Autor</label>
                            <select
                                v-model="form.user_id"
                                id="user_id"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option v-if="users.length === 0" disabled value="">Cargando autores...</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea v-model="form.description" id="description" rows="2" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    <button type="submit" :disabled="form.processing" class="w-full sm:w-auto inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 mt-2">
                        Crear Ticket
                    </button>
                </form>
            </div>
            <!-- Fin de las dos columnas -->
        </div>

        <!-- Listado de Tickets y Filtros -->
        <div class="p-4 sm:p-8 bg-white shadow-xl rounded-lg">
            <h2 class="text-2xl font-semibold mb-6 text-gray-800">Tickets ({{ tickets.total || tickets.data.length }})</h2>

            <div class="mb-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0 md:space-x-4">

                <div class="flex items-center space-x-2 w-full md:w-auto">
                    <label for="status_filter" class="text-sm font-medium text-gray-700">Estado:</label>
                    <select
                        v-model="currentFilters.status"
                        class="form-select rounded-md border-gray-300 shadow-sm text-sm"
                    >
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>

                <div class="flex items-center space-x-2 w-full md:w-auto">
                    <label for="per_page" class="text-sm font-medium text-gray-700">Mostrar:</label>
                    <select
                        v-model="currentFilters.per_page"
                        class="form-select rounded-md border-gray-300 shadow-sm text-sm"
                    >
                        <option v-for="option in perPageOptions" :key="option" :value="option">{{ option }}</option>
                    </select>
                </div>

                <div class="w-full md:w-1/3 relative">
                    <input
                        type="text"
                        placeholder="Buscar por Título o Descripción..."
                        v-model="currentFilters.search"
                        class="form-input block w-full rounded-md border-gray-300 shadow-sm text-sm pr-10"
                    >
                    <button v-if="currentFilters.search" @click="clearSearch" type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700">
                        <span class="text-lg leading-none">&times;</span>
                    </button>
                </div>

                <button
                    @click="resetFilters"
                    class="px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-500 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 w-full md:w-auto"
                >
                    Restablecer Filtros
                </button>
            </div>

            <!-- Tabla de Escritorio -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th v-for="column in sortableColumns" :key="column.key"
                                @click="setSort(column.key)"
                                :class="[
                                    'px-6 py-3 bg-gray-50 text-left text-xs font-medium uppercase tracking-wider cursor-pointer select-none relative group',
                                    currentFilters.sort_column === column.key ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-600'
                                ]"
                            >
                                <!-- CORRECCIÓN: Alineación del texto y los iconos -->
                                <span class="flex items-center justify-between">
                                    {{ column.label }}
                                    <span class="ml-2 text-base">
                                        <span v-if="currentFilters.sort_column === column.key" class="text-indigo-600">
                                            {{ currentFilters.sort_direction === 'asc' ? '▲' : '▼' }}
                                        </span>
                                        <span v-else class="text-gray-400 opacity-50 transition-opacity group-hover:opacity-100">
                                            ⇵
                                        </span>
                                    </span>
                                </span>

                                <button v-if="currentFilters.sort_column === column.key && column.key !== 'created_at'"
                                        @click.stop="clearSort"
                                        type="button"
                                        class="ml-2 text-red-400 hover:text-red-600 focus:outline-none text-sm leading-none absolute right-0 top-0 mt-3 mr-1"
                                        title="Quitar ordenación"
                                >
                                    &times;
                                </button>
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Autor</th>
                            <th class="px-6 py-3 bg-gray-50">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-gray-50">
                            <!-- Columna 1: ID -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ ticket.id }}</td>
                            <!-- Columna 2: Título y Descripción -->
                            <td class="px-6 py-4">
                                <div class="text-base font-semibold text-gray-800 break-words">{{ ticket.title }}</div>
                                <div class="text-sm text-gray-500 mt-1 break-words">{{ ticket.description }}</div>
                            </td>
                            <!-- Columna 3: Estado con indicador visual -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="{'bg-green-100 text-green-800': ticket.status === 'open', 'bg-yellow-100 text-yellow-800': ticket.status === 'in_progress', 'bg-gray-100 text-gray-800': ticket.status === 'closed'}"
                                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full items-center"
                                >
                                    <!-- Círculo de estado -->
                                    <span :class="{
                                        'bg-green-500': ticket.status === 'open',
                                        'bg-yellow-500': ticket.status === 'in_progress',
                                        'bg-gray-500': ticket.status === 'closed'
                                    }" class="w-2 h-2 rounded-full mr-2"></span>
                                    {{ ticket.status.toUpperCase().replace('_', ' ') }}
                                </span>
                            </td>
                            <!-- Columna 4: Autor -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ ticket.user ? ticket.user.name : 'N/A' }}
                            </td>
                            <!-- Columna 5: Acción -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button
                                    v-if="ticket.status !== 'closed'"
                                    @click="updateStatus(ticket)"
                                    class="text-indigo-600 hover:text-indigo-800 text-sm p-2 border rounded-md shadow-sm transition duration-150 ease-in-out bg-white hover:bg-indigo-50"
                                >
                                    Avanzar a {{ ticket.status === 'open' ? 'En Progreso' : 'Cerrado' }}
                                </button>
                                <span v-else class="text-gray-500 text-sm">Completado</span>
                            </td>
                        </tr>
                        <tr v-if="tickets.data.length === 0">
                            <td colspan="5" class="text-center p-4 text-gray-500">No hay tickets que coincidan con los filtros.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Vista de Tarjetas para Móvil -->
            <div class="md:hidden space-y-4">
                <div v-for="ticket in tickets.data" :key="ticket.id" class="p-4 border rounded-lg shadow-sm bg-gray-50">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-sm font-bold text-gray-600">#{{ ticket.id }}</span>
                        <!-- Estado con indicador visual -->
                        <span
                            :class="{'bg-green-100 text-green-800': ticket.status === 'open', 'bg-yellow-100 text-yellow-800': ticket.status === 'in_progress', 'bg-gray-100 text-gray-800': ticket.status === 'closed'}"
                            class="px-3 py-1 text-xs leading-5 font-semibold rounded-full flex items-center"
                        >
                            <!-- Círculo de estado -->
                            <span :class="{
                                'bg-green-500': ticket.status === 'open',
                                'bg-yellow-500': ticket.status === 'in_progress',
                                'bg-gray-500': ticket.status === 'closed'
                            }" class="w-2 h-2 rounded-full mr-2"></span>
                            {{ ticket.status.toUpperCase().replace('_', ' ') }}
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-indigo-700 mb-1 break-words">{{ ticket.title }}</h3>
                    <p class="text-sm text-gray-600 mb-2 break-words">{{ ticket.description }}</p>

                    <div class="text-xs text-gray-500">
                        <span class="font-medium">Autor:</span> {{ ticket.user ? ticket.user.name : 'N/A' }}
                    </div>

                    <div class="mt-3">
                        <button
                            v-if="ticket.status !== 'closed'"
                            @click="updateStatus(ticket)"
                            class="w-full text-indigo-600 hover:text-indigo-800 text-sm p-2 border rounded-md shadow-sm transition duration-150 ease-in-out bg-white hover:bg-indigo-50"
                        >
                            Avanzar a {{ ticket.status === 'open' ? 'En Progreso' : 'Cerrado' }}
                        </button>
                        <span v-else class="text-gray-500 text-sm block text-center">Completado</span>
                    </div>
                </div>

                <div v-if="tickets.data.length === 0" class="text-center p-4 text-gray-500">
                    No hay tickets que coincidan con los filtros.
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="tickets.links && tickets.links.length > 3" class="flex justify-center mt-6 overflow-x-auto">
                <template v-for="(link, index) in tickets.links" :key="index">
                    <button
                        v-if="link.url"
                        @click="changePage(link.url)"
                        v-html="link.label.replace('Previous', 'Anterior').replace('Next', 'Siguiente')"
                        class="px-3 py-1 mx-0.5 text-xs sm:text-sm rounded-md flex-shrink-0 transition duration-150"
                        :class="{
                            'bg-indigo-500 text-white shadow-md': link.active,
                            'text-gray-700 hover:bg-gray-200': !link.active
                        }"
                    />
                    <span v-else v-html="link.label.replace('Previous', 'Anterior').replace('Next', 'Siguiente')"
                        class="px-3 py-1 mx-0.5 text-xs sm:text-sm rounded-md flex-shrink-0 text-gray-400 cursor-not-allowed bg-gray-100"
                    ></span>
                </template>
            </div>

        </div>
    </div>
</template>