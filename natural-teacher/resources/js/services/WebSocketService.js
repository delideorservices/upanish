import { ref } from 'vue';

class WebSocketService {
  constructor() {
    this.socket = null;
    this.connected = ref(false);
    this.messageHandlers = new Map();
  }

  connect(sessionId) {
    // Close existing connection if any
    if (this.socket) {
      this.socket.close();
    }

    // Determine the WebSocket URL based on the current environment
    const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
    // const host = window.location.host;
    const host = `${window.location.hostname}:5000`; 
    const wsUrl = `${protocol}//${host}/ws/${sessionId}`;

    // Create a new WebSocket connection
    this.socket = new WebSocket(wsUrl);

    // Set up event handlers
    this.socket.onopen = this.handleOpen.bind(this);
    this.socket.onmessage = this.handleMessage.bind(this);
    this.socket.onclose = this.handleClose.bind(this);
    this.socket.onerror = this.handleError.bind(this);

    return this.socket;
  }

  handleOpen(event) {
    console.log('WebSocket connection established');
    this.connected.value = true;
    this.notifyHandlers('connection', { status: 'connected' });
  }

  handleMessage(event) {
    try {
      const data = JSON.parse(event.data);
      this.notifyHandlers('message', data);
    } catch (error) {
      // Handle streaming text (non-JSON)
      this.notifyHandlers('stream', event.data);
    }
  }

  handleClose(event) {
    console.log('WebSocket connection closed', event);
    this.connected.value = false;
    this.notifyHandlers('connection', { status: 'disconnected' });
  }

  handleError(error) {
    console.error('WebSocket error:', error);
    this.notifyHandlers('error', error);
  }

  sendMessage(message) {
    if (!this.socket || this.socket.readyState !== WebSocket.OPEN) {
      throw new Error('WebSocket is not connected');
    }

    const payload = typeof message === 'string' ? message : JSON.stringify(message);
    this.socket.send(payload);
  }

  addHandler(type, handler) {
    this.messageHandlers.set(type, handler);
  }

  removeHandler(type) {
    this.messageHandlers.delete(type);
  }

  notifyHandlers(type, data) {
    const handler = this.messageHandlers.get(type);
    if (handler) {
      handler(data);
    }
  }

  disconnect() {
    if (this.socket) {
      this.socket.close();
      this.socket = null;
      this.connected.value = false;
    }
  }
}

export default new WebSocketService();