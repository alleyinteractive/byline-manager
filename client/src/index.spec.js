/* Your Tests here */

/* eslint-disable  no-return-assign, global-require */

let outputData = '';

const storeLog = (inputs) => (outputData += inputs);

describe('test admin ui index', () => {
  it('should console log', () => {
    /* eslint-disable-next-line */
    console.log = jest.fn(storeLog);
    require('./index.js');
    expect(outputData).toBe('Admin UI loaded');
  });
});
