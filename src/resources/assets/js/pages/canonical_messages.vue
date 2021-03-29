<template>
  <div class=" card-outline-info">
    <div class="card-header">
			<h4 class="m-b-0 text-white">Mensagens canonicas</h4>
      <button 
        @click="showModal = true"
        class="btn btn-success"
      >Adicionar mensagem</button>
		</div>
    <div class="card-block">
      <table class="table table-condensed">
        <tr>
          <th>Shortcode</th>
          <th>Mensagem</th>
        </tr>
        <tr v-for="(item, index) in messages" :key="index">
          <td>{{ item.shortcode }}</td>
          <td>{{ item.message }}</td>
        </tr>
      </table>
    </div>

    <div v-if="showModal">
      <transition name="modal">
        <div class="modal-mask">
            <div class="modal-wrapper">
                <div class="modal-container">
                    <div class="modal-header">
                        <h3>Adicionar nova mensagem</h3>
                        <a href="#"
                          @click="showModal = false"
                        >X</a>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">
                                Código
                            </label>
                            <input v-model="shortcode" class="form-control" type="text" placeholder="Código">
                        </div>
                        <div class="form-group">
                            <label class="control-label">
                                Mensagem
                            </label>
                            <input v-model="message" class="form-control" type="text" placeholder="Mensagem">
                        </div>

                    </div>
                    
                    <div class="modal-footer">            
                        <div class="message-footer">
                          <button @click="saveMessage" class="btn btn-success">Enviar</button>
                        </div>                    
                    </div>
                </div>
            </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      showModal: false,
      messages: [],
      shortcode: '',
      message: ''
    }
  },
  methods: {
    async getMessages() {
      try {
        const response = await axios.get('/admin/lib/api/canonical_messages');
        const { data } = response;
        this.messages = data.messages;
      } catch (error) {
        console.log('getMessages', error);
      }
    },
    async saveMessage(data) {
      try {
        const response = await axios.post('/admin/lib/api/save_canonical', {
          shortcode: this.shortcode,
          message: this.message
        });

        location.reload();
      } catch (error) {
        console.log('saveMessage', error);
      }
    }
  },
  created() {
    this.getMessages();
  }
}
</script>

<style>
.modal-mask {
  position: fixed;
  z-index: 9998;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, .5);
  display: table;
  transition: opacity .3s ease;
}

.modal-wrapper {
  display: table-cell;
  vertical-align: middle;
}

.modal-container {
  width: 100%;
  max-width: 500px;
  margin: 0px auto;
  background-color: #fff;
  border-radius: 2px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
  transition: all .3s ease;
  font-family: Helvetica, Arial, sans-serif;
}

.card-header {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
}

.modal-header h3 {
  margin-top: 0;
}

.modal-header a {
  color: red;
  font-size: 22px;
}


.modal-default-button {
  float: right;
}

.modal-enter {
  opacity: 0;
}

.modal-leave-active {
  opacity: 0;
}

.modal-enter .modal-container,
.modal-leave-active .modal-container {
  -webkit-transform: scale(1.1);
  transform: scale(1.1);
}

.message-footer {
  width: 100%;
}
</style>