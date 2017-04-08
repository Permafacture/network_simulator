def writer():
    """A coroutine that writes data *sent* to it to fd, socket, etc."""
    while True:
        w = (yield)
        print('>> ', w)

def writer_wrapper(coro):
    yield from coro

w = writer()
wrap = writer_wrapper(w)
wrap.send(None)  # prine the coroutine
for i in range(4):
    wrap.send(i)
