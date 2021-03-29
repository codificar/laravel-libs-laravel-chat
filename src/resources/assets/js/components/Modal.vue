
<template>
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-wrapper">
                <div class="modal-container">
                    <div class="modal-header">
                        <h3>Adicionar nova mensagem</h3>
                        <button class="btn btn-danger" @click="$emit('close')">
                            Fechar
                        </button> 
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label" for="email">
                                Com quem
                            </label>
                            <select v-model="who" class="form-control">
                                <option value="provider">Prestador</option>
                            </select>
                        </div>

                        <div class="form-group">
                          <autocomplete
                            :source="getWhoUrlString()"
                            method="get"
                            input-class="form-control"
                            name="receiver_id"
                            placeholder="Procurar"
                            results-property="users"
                            :results-display="formattedAutocomplete"
                            @selected="selectReceiver"
                            @clear="clearSelectReceiver"
                          ></autocomplete>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="email">
                                Tipo de mensagem
                            </label>
                            <select v-model="selectedMessageIndex" class="form-control">
                                <option value=''>Selecione...</option>
                                <option 
                                  v-for="(item, index) in canonical_messages"
                                  :key="index"
                                  :value="index"
                                >
                                  {{ item.message }}
                                </option>
                            </select>
                        </div>

                    </div>
                    
                    <div class="modal-footer">            
                       <div class="message-footer chat-send-message-footer">
                          <input v-model="messageText" type="text" placeholder="Enviar">
                          <a href="#" @click="sendMessage">
                            <i class="mdi mdi-send"></i>
                          </a>
                      </div>                    
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
import autocomplete from 'vuejs-auto-complete';
import axios from 'axios';

export default {
  props: [
    'canonicalMessages',
    'user'
  ],
  components: {
    autocomplete
  },
  data() {
    return {
      canonical_messages: [],
      selectedMessageIndex: '',
      who: 'provider',
      receiver_id: '',
      messageText: '',
      id: '',
      token: ''
    }
  },
  methods: {
    async sendMessage() {
      try {
        const params = {
          id: this.id,
          token: this.token,
          receiver: this.receiver_id,
          message: this.messageText
        };

        const response = await axios.post('/api/libs/set_direct_message', params);
        const { data } = response;
        this.$emit('modalSendMessage', data);
      } catch (error) {
        console.log('sendMessage', error);
      }
    },
    getWhoUrlString() {
      return `/admin/lib/api/get_user?type=${this.who}&name=`;
    },
    selectedMessage() {
      if (this.selectedMessageIndex !== '') {
        const message = this.canonical_messages[this.selectedMessageIndex];
        this.messageText = message.message;
      }
    },
    formattedAutocomplete (result) {
			return result.name;
		},
    selectReceiver (result) {
			const { selectedObject } = result;
			this.receiver_id = selectedObject.id;
    },
    clearSelectReceiver () {
			this.receiver_id = '';
		}
  },
  watch: {
    selectedMessageIndex: function() {
      this.selectedMessage();
    }
  },
  created() {
    this.canonical_messages = this.canonicalMessages;
    
    if (this.user) {
      this.id = this.user.id;
      this.token = this.user.id;
    }
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

.modal-header h3 {
  margin-top: 0;
  color: #009efb;
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