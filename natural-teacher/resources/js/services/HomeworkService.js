import axios from 'axios';

class HomeworkService {
  async submitHomework(data) {
    try {
      const response = await axios.post('/api/homework/submit', data);
      return response.data;
    } catch (error) {
      console.error('Failed to submit homework:', error);
      throw error;
    }
  }

  async getHomeworkResponse(homeworkId) {
    try {
      const response = await axios.get(`/api/homework/${homeworkId}/response`);
      return response.data;
    } catch (error) {
      console.error('Failed to get homework response:', error);
      throw error;
    }
  }

  async getHomeworkFeedback(homeworkId) {
    try {
      const response = await axios.get(`/api/homework/${homeworkId}/feedback`);
      return response.data;
    } catch (error) {
      console.error('Failed to get homework feedback:', error);
      throw error;
    }
  }
}

export default new HomeworkService();