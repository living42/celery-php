from celery import Celery
from kombu import Queue

app = Celery(__name__)

app.conf.update(task_queues=[Queue('celery'), Queue('default')])


@app.task
def greet(name=None, hello='hello'):
    if name:
        return '{hello}, {name}'.format(**locals())
    return hello


@app.task
def bad():
    1 / 0
