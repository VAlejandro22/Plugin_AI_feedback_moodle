# Guía Rápida de Uso - Plugin AI Feedback

## Para Profesores

### 1. Configurar API Key Global (Una vez)

1. Inicia sesión como administrador
2. Ve a: **Administración del sitio** → **Plugins** → **Feedback de tareas** → **AI Feedback**
3. Ingresa tu **API Key de OpenAI**
4. Selecciona el **modelo** (recomendado: gpt-3.5-turbo)
5. Guarda cambios

### 2. Crear Tarea con Feedback de IA

1. En tu curso, activa **Edición**
2. Agrega una actividad → **Tarea**
3. Completa la configuración básica:
   - Nombre de la tarea
   - Descripción (será usada por la IA)
   - Configuración de entrega

4. En **Tipos de feedback**, expande:
   - ☑️ **Comentarios de retroalimentación**
   - ☑️ **AI Feedback**

5. En la sección **AI Feedback**:
   - ☑️ **Activar feedback automático con IA**
   - Clic en **📄 Agregar archivo** para subir la rúbrica (PDF)
   - (Opcional) Ingresa una API Key específica para esta tarea
   - Selecciona el modelo de IA

6. **Guarda y muestra**

### 3. Preparar la Rúbrica (Importante)

La rúbrica debe ser un PDF con:
- Criterios de evaluación claros
- Escala de calificación (ej: 0-100)
- Puntos por criterio
- Descripción de niveles de logro

**Ejemplo de estructura:**

```
RÚBRICA DE EVALUACIÓN - Ensayo Argumentativo

Criterio 1: Estructura (30 puntos)
- Excelente (25-30): Introducción clara, desarrollo lógico, conclusión sólida
- Bueno (20-24): Estructura presente pero con pequeños problemas
- Regular (15-19): Estructura básica, falta coherencia
- Insuficiente (0-14): Sin estructura clara

Criterio 2: Argumentación (40 puntos)
- Excelente (35-40): Argumentos sólidos con evidencia
- ...

Criterio 3: Redacción (30 puntos)
...

Total: 100 puntos
```

### 4. Monitorear Entregas

1. Ve a la tarea
2. Clic en **Ver/Calificar todas las entregas**
3. Verás las entregas con:
   - ✅ Calificación generada automáticamente
   - 💬 Feedback de la IA

4. Puedes **editar** la calificación o feedback si lo deseas

### 5. Modificar Feedback de IA (Opcional)

1. Clic en la entrega del estudiante
2. Verás el feedback generado (marcado como "Feedback generado automáticamente por IA")
3. Puedes:
   - Cambiar la calificación
   - Agregar comentarios adicionales
   - Modificar el feedback existente

---

## Para Estudiantes

### 1. Subir Entrega

1. Accede a la tarea asignada
2. Clic en **Agregar entrega**
3. **Sube tu archivo**:
   - Formatos aceptados: TXT, PDF, DOCX
   - Arrastra y suelta o selecciona archivo
4. Clic en **Guardar cambios**

### 2. Ver Feedback

1. Espera 10-30 segundos (tiempo de procesamiento)
2. Actualiza la página
3. Verás:
   - 📊 Tu calificación
   - 💬 Feedback detallado de la IA
   - ✅ Estado: "Calificada"

### 3. Interpretar el Feedback

El feedback incluirá:
- Evaluación por criterios de la rúbrica
- Puntos fuertes de tu entrega
- Áreas de mejora
- Sugerencias específicas
- Calificación final

---

## Ejemplos de Uso

### Ejemplo 1: Ensayo Académico

**Configuración:**
- Tipo de archivo: DOCX, PDF
- Rúbrica: Estructura, argumentación, redacción, citas
- Modelo: GPT-4 (mayor calidad)

**Resultado:**
```
Calificación: 78/100

Feedback generado automáticamente por IA:

Fortalezas:
- Excelente estructura con introducción clara y conclusión sólida
- Buenos argumentos en los párrafos 2 y 3 con evidencia relevante
- Uso adecuado de citas en formato APA

Áreas de mejora:
- El párrafo 4 carece de conexión lógica con el anterior
- Algunas ideas podrían desarrollarse más profundamente
- Revisar ortografía en la página 3, línea 15

Calificación por criterios:
- Estructura: 25/30
- Argumentación: 30/40
- Redacción: 23/30

Sugerencias: Reforzar la coherencia entre párrafos...
```

### Ejemplo 2: Código de Programación

**Configuración:**
- Tipo de archivo: TXT (código fuente)
- Rúbrica: Funcionalidad, estilo, comentarios, eficiencia
- Modelo: GPT-3.5-turbo

**Resultado:**
```
Calificación: 85/100

Feedback generado automáticamente por IA:

El código implementa correctamente la funcionalidad solicitada.
Puntos destacables:
- Uso apropiado de funciones y modularización
- Buenos comentarios explicativos
- Manejo adecuado de excepciones

Sugerencias de mejora:
- Optimizar el bucle en la línea 45 (complejidad O(n²))
- Considerar usar list comprehension en lugar del bucle for
- Agregar validación de entrada en la función principal
...
```

---

## Preguntas Frecuentes

### ¿Cuánto tarda en generar el feedback?
Entre 10-30 segundos dependiendo del tamaño del archivo y el modelo.

### ¿Qué pasa si el archivo es muy grande?
Los archivos muy grandes (>5MB) pueden fallar. Recomendamos archivos menores a 2MB.

### ¿El estudiante ve la rúbrica?
No automáticamente. Debes compartirla en la descripción de la tarea si lo deseas.

### ¿Puedo desactivar el feedback de IA después?
Sí, edita la tarea y desmarca la opción en la configuración del plugin.

### ¿La IA puede equivocarse?
Sí, por eso es importante **revisar** las calificaciones generadas y ajustarlas si es necesario.

### ¿Qué modelo usar?
- **GPT-3.5-turbo**: Más rápido y económico, buena calidad
- **GPT-4**: Mejor calidad, más caro, más lento
- **GPT-4-turbo**: Equilibrio entre calidad y velocidad

### ¿Los estudiantes saben que fue evaluado por IA?
Sí, el feedback incluye el texto "Feedback generado automáticamente por IA".

---

## Mejores Prácticas

### ✅ DO (Hacer)

1. **Revisar siempre** el feedback generado antes de que lo vea el estudiante
2. Usar **rúbricas claras y detalladas**
3. **Probar** con una entrega de ejemplo primero
4. Complementar con feedback personal cuando sea necesario
5. Informar a los estudiantes sobre el uso de IA

### ❌ DON'T (No hacer)

1. Confiar 100% en la IA sin revisión
2. Usar rúbricas vagas o ambiguas
3. Evaluar trabajos creativos puramente subjetivos
4. Usar en exámenes de alto impacto sin supervisión
5. Olvidar que la IA puede tener sesgos

---

## Solución de Problemas Comunes

### "No veo el feedback después de subir"
- Espera 30 segundos y actualiza la página
- Revisa que el archivo sea TXT, PDF o DOCX
- Verifica que la API Key esté configurada

### "Error al procesar la entrega"
- Verifica que la rúbrica esté cargada
- Comprueba que la API Key sea válida
- Revisa que el archivo no esté corrupto

### "La calificación parece incorrecta"
- Edítala manualmente (clic en la calificación)
- Revisa la rúbrica por ambigüedades
- Considera cambiar a GPT-4 para mejor precisión

---

Para más ayuda, consulta el README.md o contacta al administrador del sistema.
