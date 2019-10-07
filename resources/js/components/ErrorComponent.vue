<template>

    <div class="modal">
        <div class="modal-mask">
            <div class="modal-wrapper">
                <div class="modal-container">
                    <div class="modal-body text-center text-danger">
                        <b>Ошибка загрузки файла</b>
                    </div>
                    <div class="modal-footer text-center">
                        <button class="btn btn-outline-danger btn-sm" @click="cancel()">
                            Закрыть
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
    export default {
        methods: {
            cancel() {
                this.$emit('cancel');
            }
        }
    }
</script>